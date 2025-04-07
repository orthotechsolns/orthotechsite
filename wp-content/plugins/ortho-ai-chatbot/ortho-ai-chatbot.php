<?php

/**
 * Plugin Name: Ortho AI Chatbot
 * Description: AI-powered chatbot using Together API
 * Version: 1.1.0
 * Author: Akash Ramlogan
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

define('PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PLUGIN_URL', plugin_dir_url(__FILE__));

require_once PLUGIN_DIR . 'inc/settings.php';
require_once PLUGIN_DIR . 'inc/api-handler.php';

/**
 * Set default options on plugin activation
 */
function activate()
{
    $settings = array(
        'api_key' => '',
        'bot_name' => 'AI Assistant',
        'welcome_message' => 'Hi there! How can I help you today?',
        'system_prompt' => 'You are a helpful assistant for a website.',
        'model' => 'meta-llama/Llama-3.3-70B-Instruct-Turbo-Free'
    );

    update_option('settings', $settings);
}
register_activation_hook(__FILE__, 'activate');

function enqueue_scripts()
{
    wp_enqueue_style('dashicons');
    wp_enqueue_style('chatbot-styles', PLUGIN_URL . 'assets/css/styles.css');
    wp_enqueue_script('chatbot-script', PLUGIN_URL . 'assets/js/chatbotscripts.js', array(), '1.0', true);

    $settings = get_option('settings', []);
    wp_localize_script('chatbot-script', 'chatbotData', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('chatbot-ajax-nonce'),
        'bot_name' => $settings['bot_name'] ?? 'AI Assistant',
        'welcome_message' => $settings['welcome_message'] ?? 'Hi there! How can I help you today?'
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_scripts');

function add_chatbot_to_page()
{
    $settings = get_option('settings', []);
    $bot_name = $settings['bot_name'] ?? 'AI Assistant';
    $welcome_message = $settings['welcome_message'] ?? 'Hi there! How can I help you today?';

?>
    <div id="chatbot" class="chatbot">
        <div class="chat-header">
            <div class="chat-title"><?php echo esc_html($bot_name); ?></div>
            <div class="chat-controls">
                <button type="button" class="close-btn"><span class="dashicons dashicons-no-alt"></span></button>
            </div>
        </div>
        <div class="chat-body">
            <div class="chat-messages">
                <div class="message bot-message">
                    <div class="message-content"><?php echo esc_html($welcome_message); ?></div>
                </div>
            </div>
        </div>
        <div class="chat-footer">
            <div class="chat-form">
                <textarea class="chat-input" placeholder="Type your message here..." rows="1"></textarea>
                <button type="button" class="send-btn">
                    <span class="dashicons dashicons-arrow-right-alt"></span>
                </button>
            </div>
        </div>
    </div>
    <button type="button" id="chat-toggle" class="chat-toggle">
        <span class="dashicons dashicons-format-chat"></span>
    </button>
<?php
}
add_action('wp_footer', 'add_chatbot_to_page');

function ajax_chat_message()
{
    $message = sanitize_textarea_field($_POST['message'] ?? '');

    $response = call_ai_api($message);

    wp_send_json_success(['response' => $response]);
}
add_action('wp_ajax_chat_message', 'ajax_chat_message');
add_action('wp_ajax_nopriv_chat_message', 'ajax_chat_message');
