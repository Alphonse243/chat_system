// app.js

document.addEventListener('DOMContentLoaded', function() {
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const messagesContainer = document.getElementById('messages');

    sendButton.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', function(e) {
        if (e.which === 13) sendMessage();
    });

    function sendMessage() {
        const message = messageInput.value.trim();
        if (!message) return;

        const messageHtml = `
            <div class="message sent">
                <div class="message-content">
                    <div class="message-text">${message}</div>
                    <div class="message-time">${new Date().toLocaleTimeString()}</div>
                </div>
            </div>
        `;

        messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
        messageInput.value = '';
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});