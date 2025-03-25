<?php
/**
 * Plugin Name: Ortho AI Chatbot
 * Plugin URI: https://yourwebsite.com/ortho-ai-chatbot
 * Description: An AI-powered chatbot for providing real-time responses to user queries.
 * Version: 1.0.0
 * Author: Akash Ramlogan
 * Author URI: https://yourwebsite.com
 * Text Domain: ortho-ai-chatbot
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('OAC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OAC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OAC_PLUGIN_VERSION', '1.0.0');

// Include required files
require_once OAC_PLUGIN_DIR . 'inc/settings.php';
require_once OAC_PLUGIN_DIR . 'inc/api-handler.php';
require_once OAC_PLUGIN_DIR . 'inc/shortcodes.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'oac_activate');
register_deactivation_hook(__FILE__, 'oac_deactivate');

/**
 * Plugin activation function
 */
function oac_activate() {
    // Create database tables if needed
    oac_create_tables();
    
    // Set default options
    $default_settings = array(
        'enable_chatbot' => 1,
        'bot_name' => 'Ortho Assistant',
        'welcome_message' => 'Hi there! I\'m your Ortho Assistant. How can I help you today?',
        'api_key' => '',
        'model' => 'gpt-3.5-turbo',
        'display_pages' => array('faq'),
        'position' => 'bottom-right',
        'theme_color' => '#0073aa',
    );
    
    // Only set default options if they don't exist
    if (!get_option('oac_settings')) {
        update_option('oac_settings', $default_settings);
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Plugin deactivation function
 */
function oac_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Create database tables for storing chat history and FAQ data
 */
function oac_create_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Table for chat history
    $table_name = $wpdb->prefix . 'oac_chat_history';
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        session_id varchar(50) NOT NULL,
        user_id mediumint(9) DEFAULT 0,
        user_message text NOT NULL,
        bot_response text NOT NULL,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    // Table for FAQ data
    $table_name_faq = $wpdb->prefix . 'oac_faqs';
    
    $sql .= "CREATE TABLE $table_name_faq (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        question text NOT NULL,
        answer text NOT NULL,
        category varchar(50) DEFAULT 'general',
        priority int(2) DEFAULT 0,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Load plugin text domain for translations
 */
function oac_load_textdomain() {
    load_plugin_textdomain('ortho-ai-chatbot', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'oac_load_textdomain');

/**
 * Enqueue scripts and styles
 */
function oac_enqueue_scripts() {
    // Get settings
    $settings = get_option('oac_settings');
    
    // Only load on selected pages, or everywhere if no page is selected
    $display_pages = isset($settings['display_pages']) ? $settings['display_pages'] : array();
    $load_scripts = false;
    
    if (empty($display_pages)) {
        $load_scripts = true;
    } else {
        // Check if current page is in the display pages
        foreach ($display_pages as $page) {
            if ((is_page($page)) || ($page === 'home' && is_front_page()) || ($page === 'faq' && is_page('faq'))) {
                $load_scripts = true;
                break;
            }
        }
    }
    
    if (!$load_scripts) {
        return;
    }
    
    // Enqueue CSS
    wp_enqueue_style('oac-styles', OAC_PLUGIN_URL . 'assets/css/oac-styles.css', array(), OAC_PLUGIN_VERSION);
    
    // Enqueue JS
    wp_enqueue_script('oac-scripts', OAC_PLUGIN_URL . 'assets/js/oac-scripts.js', array('jquery'), OAC_PLUGIN_VERSION, true);
    
    // Localize script with settings and AJAX URL
    wp_localize_script('oac-scripts', 'oac_data', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('oac-ajax-nonce'),
        'settings' => array(
            'bot_name' => isset($settings['bot_name']) ? $settings['bot_name'] : 'Ortho Assistant',
            'welcome_message' => isset($settings['welcome_message']) ? $settings['welcome_message'] : 'Hi there! How can I help you today?',
            'position' => isset($settings['position']) ? $settings['position'] : 'bottom-right',
            'theme_color' => isset($settings['theme_color']) ? $settings['theme_color'] : '#0073aa',
        ),
    ));
}
add_action('wp_enqueue_scripts', 'oac_enqueue_scripts');

/**
 * Add chatbot HTML to footer
 */
function oac_add_chatbot_to_footer() {
    // Get settings
    $settings = get_option('oac_settings');
    
    // Check if chatbot is enabled
    if (!isset($settings['enable_chatbot']) || !$settings['enable_chatbot']) {
        return;
    }
    
    // Check if should display on current page
    $display_pages = isset($settings['display_pages']) ? $settings['display_pages'] : array();
    $display_chatbot = false;
    
    if (empty($display_pages)) {
        $display_chatbot = true;
    } else {
        // Check if current page is in the display pages
        foreach ($display_pages as $page) {
            if ((is_page($page)) || ($page === 'home' && is_front_page()) || ($page === 'faq' && is_page('faq'))) {
                $display_chatbot = true;
                break;
            }
        }
    }
    
    if (!$display_chatbot) {
        return;
    }
    
    // Get chatbot position and theme color
    $position = isset($settings['position']) ? $settings['position'] : 'bottom-right';
    $theme_color = isset($settings['theme_color']) ? $settings['theme_color'] : '#0073aa';
    
    // Output chatbot HTML
    ?>
    <div id="oac-chatbot" class="oac-chatbot oac-position-<?php echo esc_attr($position); ?>" style="--oac-theme-color: <?php echo esc_attr($theme_color); ?>;">
        <div class="oac-chat-header">
            <div class="oac-chat-title"><?php echo esc_html(isset($settings['bot_name']) ? $settings['bot_name'] : 'Ortho Assistant'); ?></div>
            <div class="oac-chat-controls">
                <button type="button" class="oac-minimize-btn"><span class="dashicons dashicons-minus"></span></button>
                <button type="button" class="oac-close-btn"><span class="dashicons dashicons-no-alt"></span></button>
            </div>
        </div>
        <div class="oac-chat-body">
            <div class="oac-chat-messages">
                <div class="oac-message oac-bot-message">
                    <div class="oac-message-content">
                        <?php echo esc_html(isset($settings['welcome_message']) ? $settings['welcome_message'] : 'Hi there! How can I help you today?'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="oac-chat-footer">
            <textarea class="oac-chat-input" placeholder="<?php esc_attr_e('Type your message here...', 'ortho-ai-chatbot'); ?>"></textarea>
            <button type="button" class="oac-send-btn"><span class="dashicons dashicons-arrow-right-alt"></span></button>
        </div>
    </div>
    <button type="button" id="oac-chat-toggle" class="oac-chat-toggle oac-position-<?php echo esc_attr($position); ?>" style="--oac-theme-color: <?php echo esc_attr($theme_color); ?>;">
        <span class="dashicons dashicons-format-chat"></span>
    </button>
    <?php
}
add_action('wp_footer', 'oac_add_chatbot_to_footer');

/**
 * AJAX handler for chat messages
 */
function oac_handle_chat_message() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'oac-ajax-nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    // Get message from request
    $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
    
    if (empty($message)) {
        wp_send_json_error('Empty message');
    }
    
    // First check if we can find an answer in the FAQ database
    $faq_response = oac_find_faq_answer($message);
    
    if ($faq_response) {
        $response = $faq_response;
    } else {
        // If no FAQ answer found, try to get a response from the AI API
        $response = oac_get_ai_response($message, $session_id);
        
        // If API fails, fall back to a default response
        if (!$response) {
            $response = 'I apologize, but I\'m having trouble processing your request. Please try again later or contact our support team for immediate assistance.';
        }
    }
    
    // Save the conversation to the database
    oac_save_conversation($message, $response, $session_id);
    
    // Return the response
    wp_send_json_success(array(
        'message' => $message,
        'response' => $response,
    ));
}
add_action('wp_ajax_oac_chat_message', 'oac_handle_chat_message');
add_action('wp_ajax_nopriv_oac_chat_message', 'oac_handle_chat_message');

/**
 * Find an answer in the FAQ database
 * 
 * @param string $message The user's message
 * @return string|false The answer if found, false otherwise
 */
function oac_find_faq_answer($message) {
    global $wpdb;
    
    // Get all FAQs
    $faqs = $wpdb->get_results("SELECT question, answer FROM {$wpdb->prefix}oac_faqs ORDER BY priority DESC");
    
    if (empty($faqs)) {
        return false;
    }
    
    // Simple matching algorithm - check if the message contains the FAQ question
    foreach ($faqs as $faq) {
        // Convert to lowercase for case-insensitive matching
        $question_lower = strtolower($faq->question);
        $message_lower = strtolower($message);
        
        // Check if message contains the question or vice versa
        if (strpos($message_lower, $question_lower) !== false || strpos($question_lower, $message_lower) !== false) {
            return $faq->answer;
        }
    }
    
    return false;
}

/**
 * Get a response from the AI API
 * 
 * @param string $message The user's message
 * @param string $session_id The session ID for context
 * @return string The AI response
 */
function oac_get_ai_response($message, $session_id) {
    // Get previous conversation for context
    $conversation = oac_get_previous_conversation($session_id);
    
    // Include the API handler
    require_once OAC_PLUGIN_DIR . 'inc/api-handler.php';
    
    // Get response from API
    return oac_call_ai_api($message, $conversation);
}

/**
 * Get previous conversation for context
 * 
 * @param string $session_id The session ID
 * @return array Previous conversation messages
 */
function oac_get_previous_conversation($session_id) {
    global $wpdb;
    
    if (empty($session_id)) {
        return array();
    }
    
    // Get the last few messages from this session
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT user_message, bot_response FROM {$wpdb->prefix}oac_chat_history 
             WHERE session_id = %s 
             ORDER BY timestamp DESC 
             LIMIT 5",
            $session_id
        )
    );
    
    if (empty($results)) {
        return array();
    }
    
    // Format conversation for API
    $conversation = array();
    foreach (array_reverse($results) as $entry) {
        $conversation[] = array(
            'role' => 'user',
            'content' => $entry->user_message
        );
        $conversation[] = array(
            'role' => 'assistant',
            'content' => $entry->bot_response
        );
    }
    
    return $conversation;
}

/**
 * Save conversation to database
 * 
 * @param string $message The user's message
 * @param string $response The bot's response
 * @param string $session_id The session ID
 */
function oac_save_conversation($message, $response, $session_id) {
    global $wpdb;
    
    // Generate a session ID if not provided
    if (empty($session_id)) {
        $session_id = md5(uniqid() . time());
    }
    
    // Get current user ID if logged in
    $user_id = get_current_user_id();
    
    // Insert into database
    $wpdb->insert(
        $wpdb->prefix . 'oac_chat_history',
        array(
            'session_id' => $session_id,
            'user_id' => $user_id,
            'user_message' => $message,
            'bot_response' => $response,
            'timestamp' => current_time('mysql')
        ),
        array(
            '%s',
            '%d',
            '%s',
            '%s',
            '%s'
        )
    );
    
    return $session_id;
}