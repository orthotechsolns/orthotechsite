<?php

function call_ai_api($message) {
    $settings = get_option('settings', []);
    $api_key = $settings['api_key'] ?? '';
    
    if (empty($api_key)) {
        return 'Please configure your Together AI API key in the plugin settings.';
    }
    
    $model = $settings['model'] ?? 'meta-llama/Llama-3.3-70B-Instruct-Turbo-Free';
    $system_prompt = $settings['system_prompt'] ?? 'You are a helpful assistant for a orthopedics supplier website.';
    
    $response = wp_remote_post('https://api.together.xyz/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ],
        'body' => json_encode([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => $message]
            ],
            'max_tokens' => 500,
            'temperature' => 0.7
        ]),
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        return 'Error: ' . $response->get_error_message();
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    return $body['choices'][0]['message']['content'] ?? 'No response generated. Please try again.';
}