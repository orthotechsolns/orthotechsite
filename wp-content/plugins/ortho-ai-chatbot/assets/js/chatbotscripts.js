/** @format */

document.addEventListener("DOMContentLoaded", function () {
  const toggle = document.getElementById("chat-toggle");
  const chatbox = document.getElementById("chatbot");
  const input = document.querySelector(".chat-input");
  const sendBtn = document.querySelector(".send-btn");
  const messages = document.querySelector(".chat-messages");
  const closeBtn = document.querySelector(".close-btn");

  function showChat() {
    chatbox.style.display = "flex";
    toggle.style.display = "none";
    input.focus();
  }

  function hideChat() {
    chatbox.style.display = "none";
    toggle.style.display = "block";
  }

  toggle.addEventListener("click", showChat);
  closeBtn.addEventListener("click", hideChat);
  sendBtn.addEventListener("click", sendMessage);

  // Send message function
  function sendMessage() {
    const text = input.value.trim();
    if (!text) return;

    // Clear input
    input.value = "";

    // Add user message
    addMessage(text, "user");

    // Add loading indicator
    const loadingDiv = document.createElement("div");
    loadingDiv.className = "message bot-message loading";
    loadingDiv.innerHTML = '<div class="message-content">...</div>';
    messages.appendChild(loadingDiv);
    scrollDown();

    // Send to server
    fetch(chatbotData.ajaxurl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body:
        "action=chat_message&nonce=" +
        chatbotData.nonce +
        "&message=" +
        encodeURIComponent(text),
    })
      .then((response) => response.json())
      .then((data) => {
        if (loadingDiv.parentNode) {
          messages.removeChild(loadingDiv);
        }

        if (data.success && data.data.response) {
          addMessage(data.data.response, "bot");
        } else {
          addMessage("Error: Could not get a response.", "bot");
        }
      })
      .catch((error) => {
        if (loadingDiv.parentNode) {
          messages.removeChild(loadingDiv);
        }
        addMessage("Connection error. Please try again.", "bot");
      });
  }

  // Add message to chat
  function addMessage(text, sender) {
    const msgDiv = document.createElement("div");
    msgDiv.className = "message " + sender + "-message";
    msgDiv.innerHTML = '<div class="message-content">' + text + "</div>";
    messages.appendChild(msgDiv);
    scrollDown();
  }

  // Scroll chat to bottom
  function scrollDown() {
    const chatBody = document.querySelector(".chat-body");
    chatBody.scrollTop = chatBody.scrollHeight;
  }
});