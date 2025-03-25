<?php
/**
 * Shortcodes for the AI Chatbot
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode for embedding the chatbot in content
 */
function oac_chatbot_shortcode($atts) {
    // Get settings
    $settings = get_option('oac_settings');
    
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'bot_name' => isset($settings['bot_name']) ? $settings['bot_name'] : 'Ortho Assistant',
            'welcome_message' => isset($settings['welcome_message']) ? $settings['welcome_message'] : 'Hi there! How can I help you today?',
            'theme_color' => isset($settings['theme_color']) ? $settings['theme_color'] : '#0073aa',
        ),
        $atts,
        'ortho_chatbot'
    );
    
    // Generate unique ID for this instance
    $chatbot_id = 'oac-chatbot-' . wp_rand(1000, 9999);
    
    // Start output buffering
    ob_start();
    
    // Output chatbot HTML
    ?>
    <div id="<?php echo esc_attr($chatbot_id); ?>" class="oac-chatbot oac-shortcode-chatbot" style="--oac-theme-color: <?php echo esc_attr($atts['theme_color']); ?>;">
        <div class="oac-chat-header">
            <div class="oac-chat-title"><?php echo esc_html($atts['bot_name']); ?></div>
            <div class="oac-chat-controls">
                <button type="button" class="oac-minimize-btn"><span class="dashicons dashicons-minus"></span></button>
                <button type="button" class="oac-close-btn"><span class="dashicons dashicons-no-alt"></span></button>
            </div>
        </div>
        <div class="oac-chat-body">
            <div class="oac-chat-messages">
                <div class="oac-message oac-bot-message">
                    <div class="oac-message-content">
                        <?php echo esc_html($atts['welcome_message']); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="oac-chat-footer">
            <textarea class="oac-chat-input" placeholder="<?php esc_attr_e('Type your message here...', 'ortho-ai-chatbot'); ?>"></textarea>
            <button type="button" class="oac-send-btn"><span class="dashicons dashicons-arrow-right-alt"></span></button>
        </div>
    </div>
    <?php
    
    // Make sure scripts and styles are loaded
    if (!wp_script_is('oac-scripts', 'enqueued')) {
        wp_enqueue_style('oac-styles', OAC_PLUGIN_URL . 'assets/css/oac-styles.css', array(), OAC_PLUGIN_VERSION);
        wp_enqueue_script('oac-scripts', OAC_PLUGIN_URL . 'assets/js/oac-scripts.js', array('jquery'), OAC_PLUGIN_VERSION, true);
        
        // Localize script with settings and AJAX URL
        wp_localize_script('oac-scripts', 'oac_data', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('oac-ajax-nonce'),
            'settings' => array(
                'bot_name' => $atts['bot_name'],
                'welcome_message' => $atts['welcome_message'],
                'theme_color' => $atts['theme_color'],
            ),
        ));
    }
    
    // Return the output
    return ob_get_clean();
}
add_shortcode('ortho_chatbot', 'oac_chatbot_shortcode');