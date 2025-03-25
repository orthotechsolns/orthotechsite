/**
 * Main JavaScript for Ortho AI Chatbot
 */
(function($) {
    'use strict';
    
    // Global variables
    let sessionId = '';
    let isProcessing = false;
    
    // Initialize chatbot functionality
    $(document).ready(function() {
        // Generate a random session ID
        sessionId = generateSessionId();
        
        // Initialize toggle buttons
        initChatToggle();
        
        // Initialize chat input
        initChatInput();
        
        // Initialize control buttons (minimize, close)
        initControlButtons();
        
        // Initialize chatbot for shortcode versions
        initShortcodeChatbots();
    });
    
    /**
     * Initialize chat toggle button
     */
    function initChatToggle() {
        $('#oac-chat-toggle').on('click', function() {
            $('#oac-chatbot').fadeIn(300);
            $(this).hide();
            
            // Scroll to bottom of chat
            scrollToBottom();
        });
    }
    
    /**
     * Initialize chat input for message sending
     */
    function initChatInput() {
        // Send message on button click
        $('.oac-send-btn').on('click', function() {
            sendMessage($(this).closest('.oac-chatbot'));
        });
        
        // Send message on Enter key (but allow Shift+Enter for new line)
        $('.oac-chat-input').on('keydown', function(e) {
            if (e.keyCode === 13 && !e.shiftKey) {
                e.preventDefault();
                sendMessage($(this).closest('.oac-chatbot'));
            }
        });
        
        // Auto-resize textarea as user types
        $('.oac-chat-input').on('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }
    
    /**
     * Initialize control buttons
     */
    function initControlButtons() {
        // Minimize button
        $('.oac-minimize-btn').on('click', function() {
            const $chatbot = $(this).closest('.oac-chatbot');
            
            // Don't minimize shortcode chatbots
            if ($chatbot.hasClass('oac-shortcode-chatbot')) {
                return;
            }
            
            $chatbot.fadeOut(300, function() {
                $('#oac-chat-toggle').fadeIn(300);
            });
        });
        
        // Close button
        $('.oac-close-btn').on('click', function() {
            const $chatbot = $(this).closest('.oac-chatbot');
            
            // Don't close shortcode chatbots
            if ($chatbot.hasClass('oac-shortcode-chatbot')) {
                return;
            }
            
            $chatbot.fadeOut(300, function() {
                $('#oac-chat-toggle').fadeIn(300);
            });
        });
    }
    
    /**
     * Initialize shortcode chatbots
     */
    function initShortcodeChatbots() {
        $('.oac-shortcode-chatbot').each(function() {
            // Ensure chat container is visible
            $(this).show();
            
            // Disable minimize and close buttons for shortcode chatbots
            $(this).find('.oac-minimize-btn, .oac-close-btn').on('click', function(e) {
                e.preventDefault();
                return false;
            });
        });
    }
    
    /**
     * Send a message to the chatbot
     */
    function sendMessage($chatbot) {
        // Get the chat input
        const $input = $chatbot.find('.oac-chat-input');
        const message = $input.val().trim();
        
        // Don't send empty messages
        if (!message || isProcessing) {
            return;
        }
        
        // Disable input while processing
        isProcessing = true;
        $input.prop('disabled', true);
        $chatbot.find('.oac-send-btn').prop('disabled', true);
        
        // Add user message to chat
        addMessage($chatbot, message, 'user');
        
        // Clear input
        $input.val('');
        $input.css('height', '');
        
        // Show typing indicator
        showTypingIndicator($chatbot);
        
        // Send message to server
        $.ajax({
            url: oac_data.ajax_url,
            type: 'POST',
            data: {
                action: 'oac_chat_message',
                message: message,
                session_id: sessionId,
                nonce: oac_data.nonce
            },
            success: function(response) {
                // Hide typing indicator
                hideTypingIndicator($chatbot);
                
                if (response.success) {
                    // Add bot response to chat
                    addMessage($chatbot, response.data.response, 'bot');
                } else {
                    // Add error message to chat
                    addMessage($chatbot, 'Sorry, there was an error processing your request. Please try again.', 'bot');
                }
                
                // Re-enable input
                isProcessing = false;
                $input.prop('disabled', false);
                $chatbot.find('.oac-send-btn').prop('disabled', false);
                $input.focus();
            },
            error: function() {
                // Hide typing indicator
                hideTypingIndicator($chatbot);
                
                // Add error message to chat
                addMessage($chatbot, 'Sorry, there was an error communicating with the server. Please try again.', 'bot');
                
                // Re-enable input
                isProcessing = false;
                $input.prop('disabled', false);
                $chatbot.find('.oac-send-btn').prop('disabled', false);
                $input.focus();
            }
        });
    }
    
    /**
     * Add a message to the chat
     */
    function addMessage($chatbot, message, sender) {
        const $messages = $chatbot.find('.oac-chat-messages');
        const messageClass = sender === 'user' ? 'oac-user-message' : 'oac-bot-message';
        
        const $message = $(`
            <div class="oac-message ${messageClass}">
                <div class="oac-message-content">
                    ${message}
                </div>
            </div>
        `);
        
        $messages.append($message);
        scrollToBottom($chatbot);
    }
    
    /**
     * Show typing indicator
     */
    function showTypingIndicator($chatbot) {
        const $messages = $chatbot.find('.oac-chat-messages');
        
        const $typing = $(`
            <div class="oac-typing">
                <div class="oac-typing-dot"></div>
                <div class="oac-typing-dot"></div>
                <div class="oac-typing-dot"></div>
            </div>
        `);
        
        $messages.append($typing);
        scrollToBottom($chatbot);
    }
    
    /**
     * Hide typing indicator
     */
    function hideTypingIndicator($chatbot) {
        $chatbot.find('.oac-typing').remove();
    }
    
    /**
     * Scroll to the bottom of the chat
     */
    function scrollToBottom($chatbot) {
        const $body = $chatbot ? $chatbot.find('.oac-chat-body') : $('.oac-chat-body');
        $body.scrollTop($body[0].scrollHeight);
    }
    
    /**
     * Generate a unique session ID
     */
    function generateSessionId() {
        return 'oac-' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
    }
    
})(jQuery);