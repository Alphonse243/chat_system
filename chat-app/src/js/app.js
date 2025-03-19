// app.js

document.addEventListener('DOMContentLoaded', function () {
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const messagesContainer = document.getElementById('messages');
    const typingIndicator = document.getElementById('typing-indicator');

    if (sendButton && messageInput && messagesContainer && typingIndicator) {
        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', function (event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        });

        messageInput.addEventListener('input', function () {
            if (messageInput.value.length > 0) {
                showTypingIndicator();
            } else {
                hideTypingIndicator();
            }
        });

        function sendMessage() {
            const messageText = messageInput.value.trim();
            if (messageText !== '') {
                // Create message element
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message', 'sent');

                const messageContentDiv = document.createElement('div');
                messageContentDiv.classList.add('message-content');

                const messageTextDiv = document.createElement('div');
                messageTextDiv.classList.add('message-text');
                messageTextDiv.textContent = messageText;

                const messageTimeDiv = document.createElement('div');
                messageTimeDiv.classList.add('message-time');
                const now = new Date();
                messageTimeDiv.textContent = `${now.getHours()}:${String(now.getMinutes()).padStart(2, '0')}`;

                messageContentDiv.appendChild(messageTextDiv);
                messageContentDiv.appendChild(messageTimeDiv);
                messageDiv.appendChild(messageContentDiv);

                messagesContainer.appendChild(messageDiv);

                // Clear input
                messageInput.value = '';

                // Hide typing indicator
                hideTypingIndicator();

                // Scroll to bottom
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }

        function showTypingIndicator() {
            typingIndicator.classList.remove('d-none');
        }

        function hideTypingIndicator() {
            typingIndicator.classList.add('d-none');
        }
    } else {
        console.error('One or more elements not found.');
    }
});