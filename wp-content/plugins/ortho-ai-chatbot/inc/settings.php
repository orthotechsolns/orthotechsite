<?php

if (!defined('ABSPATH')) exit;

// Add settings page to admin menu
add_action('admin_menu', function () {
    add_options_page(
        'AI Chatbot Settings',
        'AI Chatbot',
        'manage_options',
        'ai-chatbot',
        'render_settings_page'
    );
});


function render_settings_page()
{
    // Save settings
    if (isset($_POST['settings_submit'])) {
        update_option('settings', [
            'api_key' => sanitize_text_field($_POST['api_key'] ?? ''),
            'model' => sanitize_text_field($_POST['model'] ?? 'meta-llama/Llama-3.3-70B-Instruct-Turbo-Free'),
            'system_prompt' => sanitize_textarea_field(stripslashes($_POST['system_prompt'] ?? 'You are a helpful assistant')),
            'bot_name' => sanitize_text_field($_POST['bot_name'] ?? 'AI Assistant'),
            'welcome_message' => sanitize_textarea_field($_POST['welcome_message'] ?? 'Hi there! How can I help you today?')
        ]);
        echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
    }

    $settings = get_option('settings', []);
        $api_key = $settings['api_key'] ?? '';
        $model = $settings['model'] ?? 'meta-llama/Llama-3.3-70B-Instruct-Turbo-Free';
        $system_prompt = $settings['system_prompt'] ?? 'You are a helpful assistant.';
        $bot_name = $settings['bot_name'] ?? 'AI Assistant';
        $welcome_message = $settings['welcome_message'] ?? 'Hi there! How can I help you today?';
?>
    <div class="wrap">
        <h1>AI Chatbot Settings</h1>

        <form method="post">
            <table class="form-table">
                <tr>
                    <th>Together AI API Key</th>
                    <td>
                        <input type="password" name="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                        <p class="description">Get your API key from <a href="https://api.together.xyz/settings/api-keys" target="_blank">Together AI</a></p>
                    </td>
                </tr>
                <tr>
                    <th>AI Model</th>
                    <td>
                        <input type="text" name="model" value="<?php echo esc_attr($model); ?>" class="regular-text">
                        <p class="description">Default: meta-llama/Llama-3.3-70B-Instruct-Turbo-Free</p>
                    </td>
                </tr>
                <tr>
                    <th>System Prompt</th>
                    <td>
                        <textarea name="system_prompt" rows="3" class="large-text"><?php echo esc_textarea($system_prompt); ?></textarea>
                        <p class="description">Instructions for the chatbot to follow</p>
                    </td>
                </tr>
                <tr>
                    <th>Bot Name</th>
                    <td><input type="text" name="bot_name" value="<?php echo esc_attr($bot_name); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th>Welcome Message</th>
                    <td><textarea name="welcome_message" rows="2" class="large-text"><?php echo esc_textarea($welcome_message); ?></textarea></td>
                </tr>
            </table>

            <p><input type="submit" name="settings_submit" class="button button-primary" value="Save Settings"></p>
        </form>
    </div>
<?php
}
