// This creates the chatbot button and window
function addChatbotToPage() {
    // Create the HTML for chatbot
    const chatbotHTML = `
    <div id="chatbot-container">
        <button id="chatbot-launcher">
            <i class="fas fa-robot"></i>
        </button>
        <div id="chatbot-window">
            <div class="chatbot-header">
                <h3>AI Assistant</h3>
                <button class="chatbot-close">&times;</button>
            </div>
            <div class="chatbot-messages" id="chatbot-messages">
                <div class="message bot-message">Hello! How can I help?</div>
            </div>
            <div class="chatbot-input">
                <input type="text" id="chatbot-input-field" placeholder="Type your message...">
                <button id="chatbot-send">Send</button>
            </div>
        </div>
    </div>`;
    
    // Add it to the page
    document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    
    // Make the buttons work
    document.getElementById('chatbot-launcher').onclick = function() {
        const window = document.getElementById('chatbot-window');
        window.style.display = window.style.display === 'flex' ? 'none' : 'flex';
    };
    
    document.querySelector('.chatbot-close').onclick = function() {
        document.getElementById('chatbot-window').style.display = 'none';
    };
    
    // Send message when button clicked
    document.getElementById('chatbot-send').onclick = sendMessage;
    
    // Send message when Enter key pressed
    document.getElementById('chatbot-input-field').onkeypress = function(e) {
        if (e.key === 'Enter') sendMessage();
    };
}

// Handle sending messages
function sendMessage() {
    const input = document.getElementById('chatbot-input-field');
    const message = input.value.trim();
    
    if (message) {
        // Add user message
        addMessage(message, 'user');
        input.value = '';
        
        // Show "typing..." indicator
        showTyping();
        
        // Get response after 1 second (replace with real API call)
        setTimeout(() => {
            hideTyping();
            const response = "This is a sample response. Connect to your Python backend for real answers.";
            addMessage(response, 'bot');
        }, 1000);
    }
}

// Helper functions
function addMessage(text, sender) {
    const div = document.createElement('div');
    div.className = `message ${sender}-message`;
    div.textContent = text;
    document.getElementById('chatbot-messages').appendChild(div);
}

function showTyping() {
    const typingHTML = `<div class="typing-indicator" id="typing-indicator">
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
    </div>`;
    document.getElementById('chatbot-messages').insertAdjacentHTML('beforeend', typingHTML);
}

function hideTyping() {
    const typing = document.getElementById('typing-indicator');
    if (typing) typing.remove();
}

// Start the chatbot when page loads
document.addEventListener('DOMContentLoaded', addChatbotToPage);