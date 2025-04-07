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
    wp_enqueue_style('chatbot-styles', PLUGIN_URL . 'assets/css/styles.css', array(), '1.0.1');
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
    
    <script>
    const chatbotData = {
        restUrl: '<?php echo esc_js(rest_url('ortho-chatbot/v1/chat')); ?>',
        restNonce: '<?php echo esc_js(wp_create_nonce('wp_rest')); ?>',
        bot_name: '<?php echo esc_js($bot_name); ?>',
        welcome_message: '<?php echo esc_js($welcome_message); ?>'
    };
    
    window.addEventListener('load', function() {
        initChatbot();
    });

    function initChatbot() {
        const toggle = document.getElementById('chat-toggle');
        const chatbox = document.getElementById('chatbot');
        const input = document.querySelector('.chat-input');
        const sendBtn = document.querySelector('.send-btn');
        const messages = document.querySelector('.chat-messages');
        const closeBtn = document.querySelector('.close-btn');
        
        function showChat() {
            chatbox.style.display = 'flex';
            toggle.style.display = 'none';
            input.focus();
        }

        function hideChat() {
            chatbox.style.display = 'none';
            toggle.style.display = 'block';
        }

        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            showChat();
        });

        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            hideChat();
        });

        sendBtn.addEventListener('click', function(e) {
            e.preventDefault();
            sendMessage();
        });

        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        function sendMessage() {
            const text = input.value.trim();
            if (!text) return;

            input.value = '';
            addMessage(text, 'user');

            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'message bot-message loading';
            loadingDiv.innerHTML = '<div class="message-content">...</div>';
            messages.appendChild(loadingDiv);
            scrollDown();

            fetch(chatbotData.restUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': chatbotData.restNonce
                },
                body: JSON.stringify({
                    message: text
                })
            })
            .then((response) => response.json())
            .then((data) => {
                if (loadingDiv.parentNode) {
                    messages.removeChild(loadingDiv);
                }

                if (data.response) {
                    addMessage(data.response, 'bot');
                } else {
                    addMessage('Error: Could not get a response.', 'bot');
                }
            })
            .catch((error) => {
                if (loadingDiv.parentNode) {
                    messages.removeChild(loadingDiv);
                }
                addMessage('Connection error. Please try again.', 'bot');
            });
        }

        function addMessage(text, sender) {
            const msgDiv = document.createElement('div');
            msgDiv.className = 'message ' + sender + '-message';
            msgDiv.innerHTML = '<div class="message-content">' + text + '</div>';
            messages.appendChild(msgDiv);
            scrollDown();
        }

        function scrollDown() {
            const chatBody = document.querySelector('.chat-body');
            if (chatBody) {
                chatBody.scrollTop = chatBody.scrollHeight;
            }
        }

        hideChat();
    }
    </script>
<?php
}
add_action('wp_footer', 'add_chatbot_to_page');

function register_rest_api() {
    register_rest_route('ortho-chatbot/v1', '/chat', array(
        'methods' => 'POST',
        'callback' => 'handle_chat_message',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_rest_api');

function handle_chat_message($request) {
    $message = sanitize_textarea_field($request->get_param('message'));
    $response = call_ai_api($message);
    return array('response' => $response);
}
