<?php
/**
 * API handler for the AI Chatbot
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Call the AI API to get a response
 * 
 * @param string $message The user's message
 * @param array $conversation Previous conversation for context
 * @return string The AI response
 */
function oac_call_ai_api($message, $conversation = array()) {
    // Get settings
    $settings = get_option('oac_settings');
    
    // Check if API key is set
    $api_key = isset($settings['api_key']) ? $settings['api_key'] : '';
    if (empty($api_key)) {
        return 'API key is not configured. Please set up the AI Chatbot plugin in the admin dashboard.';
    }
    
    // Get the model to use
    $model = isset($settings['model']) ? $settings['model'] : 'gpt-3.5-turbo';
    
    // Prepare system message with context about the orthopedic business
    $system_message = 'You are a helpful assistant for an orthopedic business. Provide accurate and concise information about orthopedic products, services, recovery processes, and general orthopedic health topics. If you don\'t know the answer, suggest contacting the support team.';
    
    // Prepare conversation messages
    $messages = array(
        array(
            'role' => 'system',
            'content' => $system_message
        )
    );
    
    // Add previous conversation for context
    if (!empty($conversation)) {
        $messages = array_merge($messages, $conversation);
    }
    
    // Add the current message
    $messages[] = array(
        'role' => 'user',
        'content' => $message
    );
    
    // Prepare request data
    $request_data = array(
        'model' => $model,
        'messages' => $messages,
        'max_tokens' => 500,
        'temperature' => 0.7
    );
    
    // Make API request
    $response = wp_remote_post(
        'https://api.openai.com/v1/chat/completions',
        array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($request_data),
            'timeout' => 30
        )
    );
    
    // Check for errors
    if (is_wp_error($response)) {
        error_log('AI API Error: ' . $response->get_error_message());
        return false;
    }
    
    // Parse response
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
    // Check if response is valid
    if (!isset($response_body['choices'][0]['message']['content'])) {
        error_log('Invalid API response: ' . wp_json_encode($response_body));
        return false;
    }
    
    // Return the AI response
    return $response_body['choices'][0]['message']['content'];
}

/**
 * Generate API key input field for settings page
 */
function oac_api_key_field_callback() {
    $settings = get_option('oac_settings');
    $api_key = isset($settings['api_key']) ? $settings['api_key'] : '';
    ?>
    <input type="password" name="oac_settings[api_key]" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
    <p class="description">
        <?php _e('Enter your OpenAI API key. You can get one from <a href="https://platform.openai.com/account/api-keys" target="_blank">OpenAI\'s website</a>.', 'ortho-ai-chatbot'); ?>
    </p>
    <?php
}

/**
 * Generate model selection field for settings page
 */
function oac_model_field_callback() {
    $settings = get_option('oac_settings');
    $model = isset($settings['model']) ? $settings['model'] : 'gpt-3.5-turbo';
    ?>
    <select name="oac_settings[model]">
        <option value="gpt-3.5-turbo" <?php selected($model, 'gpt-3.5-turbo'); ?>><?php _e('GPT-3.5 Turbo (Faster, less expensive)', 'ortho-ai-chatbot'); ?></option>
        <option value="gpt-4" <?php selected($model, 'gpt-4'); ?>><?php _e('GPT-4 (More capable, more expensive)', 'ortho-ai-chatbot'); ?></option>
    </select>
    <p class="description">
        <?php _e('Select the AI model to use. GPT-4 is more capable but costs more.', 'ortho-ai-chatbot'); ?>
    </p>
    <?php
}