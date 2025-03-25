<?php
/**
 * Settings page for the AI Chatbot plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add settings page to admin menu
 */
function oac_add_settings_page() {
    add_options_page(
        __('AI Chatbot Settings', 'ortho-ai-chatbot'),
        __('AI Chatbot', 'ortho-ai-chatbot'),
        'manage_options',
        'ortho-ai-chatbot',
        'oac_render_settings_page'
    );
}
add_action('admin_menu', 'oac_add_settings_page');

/**
 * Render the settings page
 */
function oac_render_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="options.php">
            <?php
            // Output security fields
            settings_fields('oac_settings_group');
            
            // Output setting sections
            do_settings_sections('ortho-ai-chatbot');
            
            // Submit button
            submit_button();
            ?>
        </form>
        
        <div class="oac-shortcode-info">
            <h2><?php _e('Shortcode Usage', 'ortho-ai-chatbot'); ?></h2>
            <p><?php _e('You can also use a shortcode to embed the chatbot in specific locations:', 'ortho-ai-chatbot'); ?></p>
            <code>[ortho_chatbot]</code>
            
            <h3><?php _e('Available Attributes', 'ortho-ai-chatbot'); ?></h3>
            <ul>
                <li><code>bot_name</code> - <?php _e('Custom name for the chatbot (overrides settings)', 'ortho-ai-chatbot'); ?></li>
                <li><code>welcome_message</code> - <?php _e('Custom welcome message (overrides settings)', 'ortho-ai-chatbot'); ?></li>
                <li><code>theme_color</code> - <?php _e('Custom theme color in hex format (overrides settings)', 'ortho-ai-chatbot'); ?></li>
            </ul>
            
            <h3><?php _e('Example', 'ortho-ai-chatbot'); ?></h3>
            <p><code>[ortho_chatbot bot_name="Support Bot" welcome_message="How can I help you with our orthopedic products?" theme_color="#FF5733"]</code></p>
        </div>
        
        <div class="oac-faq-management">
            <h2><?php _e('FAQ Management', 'ortho-ai-chatbot'); ?></h2>
            <p><?php _e('Add frequently asked questions to improve response accuracy:', 'ortho-ai-chatbot'); ?></p>
            
            <div class="oac-faq-form">
                <h3><?php _e('Add New FAQ', 'ortho-ai-chatbot'); ?></h3>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="oac_add_faq">
                    <?php wp_nonce_field('oac_add_faq_nonce', 'oac_faq_nonce'); ?>
                    
                    <p>
                        <label for="oac_faq_question"><?php _e('Question:', 'ortho-ai-chatbot'); ?></label>
                        <input type="text" id="oac_faq_question" name="oac_faq_question" class="regular-text" required>
                    </p>
                    
                    <p>
                        <label for="oac_faq_answer"><?php _e('Answer:', 'ortho-ai-chatbot'); ?></label>
                        <textarea id="oac_faq_answer" name="oac_faq_answer" class="large-text" rows="4" required></textarea>
                    </p>
                    
                    <p>
                        <label for="oac_faq_category"><?php _e('Category:', 'ortho-ai-chatbot'); ?></label>
                        <input type="text" id="oac_faq_category" name="oac_faq_category" value="general" class="regular-text">
                    </p>
                    
                    <p>
                        <label for="oac_faq_priority"><?php _e('Priority:', 'ortho-ai-chatbot'); ?></label>
                        <input type="number" id="oac_faq_priority" name="oac_faq_priority" value="0" min="0" max="10" class="small-text">
                        <span class="description"><?php _e('Higher priority FAQs are matched first (0-10).', 'ortho-ai-chatbot'); ?></span>
                    </p>
                    
                    <?php submit_button(__('Add FAQ', 'ortho-ai-chatbot'), 'secondary', 'submit', false); ?>
                </form>
            </div>
            
            <div class="oac-faq-list">
                <h3><?php _e('Existing FAQs', 'ortho-ai-chatbot'); ?></h3>
                <?php
                global $wpdb;
                $faqs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}oac_faqs ORDER BY category, priority DESC");
                
                if (empty($faqs)) {
                    echo '<p>' . __('No FAQs added yet.', 'ortho-ai-chatbot') . '</p>';
                } else {
                    $current_category = '';
                    echo '<div class="oac-faq-items">';
                    
                    foreach ($faqs as $faq) {
                        // Display category header if it changed
                        if ($current_category != $faq->category) {
                            if (!empty($current_category)) {
                                echo '</div>'; // Close previous category
                            }
                            $current_category = $faq->category;
                            echo '<h4>' . esc_html(ucfirst($current_category)) . '</h4>';
                            echo '<div class="oac-category-faqs">';
                        }
                        
                        // Display FAQ item
                        ?>
                        <div class="oac-faq-item">
                            <div class="oac-faq-question">
                                <strong><?php echo esc_html($faq->question); ?></strong>
                                <span class="oac-faq-priority"><?php echo esc_html(__('Priority:', 'ortho-ai-chatbot') . ' ' . $faq->priority); ?></span>
                            </div>
                            <div class="oac-faq-answer"><?php echo esc_html($faq->answer); ?></div>
                            <div class="oac-faq-actions">
                                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=oac_delete_faq&faq_id=' . $faq->id), 'oac_delete_faq_' . $faq->id, 'oac_delete_nonce'); ?>" class="button button-small" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this FAQ?', 'ortho-ai-chatbot'); ?>')">
                                    <?php _e('Delete', 'ortho-ai-chatbot'); ?>
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                    
                    if (!empty($current_category)) {
                        echo '</div>'; // Close last category
                    }
                    
                    echo '</div>'; // Close faq-items
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Register settings
 */
function oac_register_settings() {
    // Register setting
    register_setting(
        'oac_settings_group',
        'oac_settings',
        'oac_sanitize_settings'
    );
    
    // General Settings section
    add_settings_section(
        'oac_general_settings',
        __('General Settings', 'ortho-ai-chatbot'),
        'oac_general_settings_callback',
        'ortho-ai-chatbot'
    );
    
    // API Settings section
    add_settings_section(
        'oac_api_settings',
        __('API Settings', 'ortho-ai-chatbot'),
        'oac_api_settings_callback',
        'ortho-ai-chatbot'
    );
    
    // Display Settings section
    add_settings_section(
        'oac_display_settings',
        __('Display Settings', 'ortho-ai-chatbot'),
        'oac_display_settings_callback',
        'ortho-ai-chatbot'
    );
    
    // Add fields to General Settings section
    add_settings_field(
        'oac_enable_chatbot',
        __('Enable Chatbot', 'ortho-ai-chatbot'),
        'oac_enable_chatbot_callback',
        'ortho-ai-chatbot',
        'oac_general_settings'
    );
    
    add_settings_field(
        'oac_bot_name',
        __('Bot Name', 'ortho-ai-chatbot'),
        'oac_bot_name_callback',
        'ortho-ai-chatbot',
        'oac_general_settings'
    );
    
    add_settings_field(
        'oac_welcome_message',
        __('Welcome Message', 'ortho-ai-chatbot'),
        'oac_welcome_message_callback',
        'ortho-ai-chatbot',
        'oac_general_settings'
    );
    
    // Add fields to API Settings section
    add_settings_field(
        'oac_api_key',
        __('API Key', 'ortho-ai-chatbot'),
        'oac_api_key_field_callback',
        'ortho-ai-chatbot',
        'oac_api_settings'
    );
    
    add_settings_field(
        'oac_model',
        __('AI Model', 'ortho-ai-chatbot'),
        'oac_model_field_callback',
        'ortho-ai-chatbot',
        'oac_api_settings'
    );
    
    // Add fields to Display Settings section
    add_settings_field(
        'oac_display_pages',
        __('Display Pages', 'ortho-ai-chatbot'),
        'oac_display_pages_callback',
        'ortho-ai-chatbot',
        'oac_display_settings'
    );
    
    add_settings_field(
        'oac_position',
        __('Chatbot Position', 'ortho-ai-chatbot'),
        'oac_position_callback',
        'ortho-ai-chatbot',
        'oac_display_settings'
    );
    
    add_settings_field(
        'oac_theme_color',
        __('Theme Color', 'ortho-ai-chatbot'),
        'oac_theme_color_callback',
        'ortho-ai-chatbot',
        'oac_display_settings'
    );
}
add_action('admin_init', 'oac_register_settings');

/**
 * Sanitize settings
 */
function oac_sanitize_settings($input) {
    $sanitized = array();
    
    // Sanitize text fields
    if (isset($input['bot_name'])) {
        $sanitized['bot_name'] = sanitize_text_field($input['bot_name']);
    }
    
    if (isset($input['welcome_message'])) {
        $sanitized['welcome_message'] = sanitize_textarea_field($input['welcome_message']);
    }
    
    if (isset($input['api_key'])) {
        $sanitized['api_key'] = sanitize_text_field($input['api_key']);
    }
    
    if (isset($input['model'])) {
        $sanitized['model'] = sanitize_text_field($input['model']);
    }
    
    // Sanitize checkboxes
    $sanitized['enable_chatbot'] = isset($input['enable_chatbot']) ? 1 : 0;
    
    // Sanitize select fields
    if (isset($input['position'])) {
        $sanitized['position'] = in_array($input['position'], array('bottom-left', 'bottom-right', 'top-left', 'top-right')) 
            ? $input['position'] 
            : 'bottom-right';
    }
    
    // Sanitize color
    if (isset($input['theme_color'])) {
        $sanitized['theme_color'] = sanitize_hex_color($input['theme_color']);
    }
    
    // Sanitize array fields
    if (isset($input['display_pages']) && is_array($input['display_pages'])) {
        $sanitized['display_pages'] = array_map('sanitize_text_field', $input['display_pages']);
    } else {
        $sanitized['display_pages'] = array();
    }
    
    return $sanitized;
}

/**
 * General Settings section callback
 */
function oac_general_settings_callback() {
    echo '<p>' . __('Configure general chatbot settings.', 'ortho-ai-chatbot') . '</p>';
}

/**
 * API Settings section callback
 */
function oac_api_settings_callback() {
    echo '<p>' . __('Configure API settings for the AI integration.', 'ortho-ai-chatbot') . '</p>';
}

/**
 * Display Settings section callback
 */
function oac_display_settings_callback() {
    echo '<p>' . __('Configure how and where the chatbot is displayed.', 'ortho-ai-chatbot') . '</p>';
}

/**
 * Enable Chatbot field callback
 */
function oac_enable_chatbot_callback() {
    $settings = get_option('oac_settings');
    $enabled = isset($settings['enable_chatbot']) ? $settings['enable_chatbot'] : 1;
    ?>
    <label>
        <input type="checkbox" name="oac_settings[enable_chatbot]" value="1" <?php checked(1, $enabled); ?>>
        <?php _e('Enable AI Chatbot on the website', 'ortho-ai-chatbot'); ?>
    </label>
    <?php
}

/**
 * Bot Name field callback
 */
function oac_bot_name_callback() {
    $settings = get_option('oac_settings');
    $bot_name = isset($settings['bot_name']) ? $settings['bot_name'] : 'Ortho Assistant';
    ?>
    <input type="text" name="oac_settings[bot_name]" value="<?php echo esc_attr($bot_name); ?>" class="regular-text">
    <?php
}

/**
 * Welcome Message field callback
 */
function oac_welcome_message_callback() {
    $settings = get_option('oac_settings');
    $welcome_message = isset($settings['welcome_message']) 
        ? $settings['welcome_message'] 
        : 'Hi there! I\'m your Ortho Assistant. How can I help you today?';
    ?>
    <textarea name="oac_settings[welcome_message]" class="large-text" rows="3"><?php echo esc_textarea($welcome_message); ?></textarea>
    <?php
}

/**
 * Display Pages field callback
 */
function oac_display_pages_callback() {
    $settings = get_option('oac_settings');
    $display_pages = isset($settings['display_pages']) ? $settings['display_pages'] : array();
    
    // Standard pages
    $standard_pages = array(
        'home' => __('Home Page', 'ortho-ai-chatbot'),
        'faq' => __('FAQ Page', 'ortho-ai-chatbot'),
    );
    
    // Get all pages
    $pages = get_pages();
    ?>
    <fieldset>
        <legend class="screen-reader-text"><?php _e('Display Pages', 'ortho-ai-chatbot'); ?></legend>
        <p>
            <label>
                <input type="checkbox" name="oac_settings[display_pages][]" value="" <?php checked(empty($display_pages), true); ?>>
                <?php _e('All Pages (leave all unchecked)', 'ortho-ai-chatbot'); ?>
            </label>
        </p>
        <?php foreach ($standard_pages as $value => $label) : ?>
            <p>
                <label>
                    <input type="checkbox" name="oac_settings[display_pages][]" value="<?php echo esc_attr($value); ?>" <?php checked(in_array($value, $display_pages), true); ?>>
                    <?php echo esc_html($label); ?>
                </label>
            </p>
        <?php endforeach; ?>
        
        <p><strong><?php _e('Specific Pages:', 'ortho-ai-chatbot'); ?></strong></p>
        <?php foreach ($pages as $page) : ?>
            <p>
                <label>
                    <input type="checkbox" name="oac_settings[display_pages][]" value="<?php echo esc_attr($page->ID); ?>" <?php checked(in_array($page->ID, $display_pages), true); ?>>
                    <?php echo esc_html($page->post_title); ?>
                </label>
            </p>
        <?php endforeach; ?>
    </fieldset>
    <?php
}

/**
 * Position field callback
 */
function oac_position_callback() {
    $settings = get_option('oac_settings');
    $position = isset($settings['position']) ? $settings['position'] : 'bottom-right';
    ?>
    <select name="oac_settings[position]">
        <option value="bottom-right" <?php selected($position, 'bottom-right'); ?>><?php _e('Bottom Right', 'ortho-ai-chatbot'); ?></option>
        <option value="bottom-left" <?php selected($position, 'bottom-left'); ?>><?php _e('Bottom Left', 'ortho-ai-chatbot'); ?></option>
        <option value="top-right" <?php selected($position, 'top-right'); ?>><?php _e('Top Right', 'ortho-ai-chatbot'); ?></option>
        <option value="top-left" <?php selected($position, 'top-left'); ?>><?php _e('Top Left', 'ortho-ai-chatbot'); ?></option>
    </select>
    <?php
}

/**
 * Theme Color field callback
 */
function oac_theme_color_callback() {
    $settings = get_option('oac_settings');
    $theme_color = isset($settings['theme_color']) ? $settings['theme_color'] : '#0073aa';
    ?>
    <input type="color" name="oac_settings[theme_color]" value="<?php echo esc_attr($theme_color); ?>">
    <?php
}

/**
 * Handle adding new FAQ
 */
function oac_handle_add_faq() {
    // Check if user has permission
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'ortho-ai-chatbot'));
    }
    
    // Verify nonce
    if (!isset($_POST['oac_faq_nonce']) || !wp_verify_nonce($_POST['oac_faq_nonce'], 'oac_add_faq_nonce')) {
        wp_die(__('Invalid nonce.', 'ortho-ai-chatbot'));
    }
    
    // Get and sanitize form data
    $question = isset($_POST['oac_faq_question']) ? sanitize_text_field($_POST['oac_faq_question']) : '';
    $answer = isset($_POST['oac_faq_answer']) ? sanitize_textarea_field($_POST['oac_faq_answer']) : '';
    $category = isset($_POST['oac_faq_category']) ? sanitize_text_field($_POST['oac_faq_category']) : 'general';
    $priority = isset($_POST['oac_faq_priority']) ? intval($_POST['oac_faq_priority']) : 0;
    
    // Validate data
    if (empty($question) || empty($answer)) {
        wp_die(__('Question and answer are required.', 'ortho-ai-chatbot'));
    }
    
    // Ensure priority is between 0 and 10
    $priority = max(0, min(10, $priority));
    
    // Insert into database
    global $wpdb;
    $result = $wpdb->insert(
        $wpdb->prefix . 'oac_faqs',
        array(
            'question' => $question,
            'answer' => $answer,
            'category' => $category,
            'priority' => $priority
        ),
        array('%s', '%s', '%s', '%d')
    );
    
    // Redirect back to settings page
    if ($result) {
        wp_redirect(add_query_arg(array('page' => 'ortho-ai-chatbot', 'message' => 'faq-added'), admin_url('options-general.php')));
    } else {
        wp_redirect(add_query_arg(array('page' => 'ortho-ai-chatbot', 'error' => 'faq-add-failed'), admin_url('options-general.php')));
    }
    exit;
}
add_action('admin_post_oac_add_faq', 'oac_handle_add_faq');

/**
 * Handle deleting FAQ
 */
function oac_handle_delete_faq() {
    // Check if user has permission
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'ortho-ai-chatbot'));
    }
    
    // Get FAQ ID
    $faq_id = isset($_GET['faq_id']) ? intval($_GET['faq_id']) : 0;
    
    if (!$faq_id) {
        wp_die(__('Invalid FAQ ID.', 'ortho-ai-chatbot'));
    }
    
    // Verify nonce
    if (!isset($_GET['oac_delete_nonce']) || !wp_verify_nonce($_GET['oac_delete_nonce'], 'oac_delete_faq_' . $faq_id)) {
        wp_die(__('Invalid nonce.', 'ortho-ai-chatbot'));
    }
    
    // Delete from database
    global $wpdb;
    $result = $wpdb->delete(
        $wpdb->prefix . 'oac_faqs',
        array('id' => $faq_id),
        array('%d')
    );
    
    // Redirect back to settings page
    if ($result) {
        wp_redirect(add_query_arg(array('page' => 'ortho-ai-chatbot', 'message' => 'faq-deleted'), admin_url('options-general.php')));
    } else {
        wp_redirect(add_query_arg(array('page' => 'ortho-ai-chatbot', 'error' => 'faq-delete-failed'), admin_url('options-general.php')));
    }
    exit;
}
add_action('admin_post_oac_delete_faq', 'oac_handle_delete_faq');

/**
 * Add settings link to plugins page
 */
function oac_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=ortho-ai-chatbot') . '">' . __('Settings', 'ortho-ai-chatbot') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(OAC_PLUGIN_DIR . 'ortho-ai-chatbot.php');
add_filter("plugin_action_links_$plugin", 'oac_add_settings_link');