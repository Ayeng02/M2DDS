<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/chat.css">
    <style>
        .badging {
    position: absolute; /* Position it relative to the chat bubble */
    top: 5px; /* Adjust this value as needed */
    right: 5px; /* Adjust this value as needed */
    background-color: #FF8225; /* Background color */
    color: #fff; /* Text color */
    border-radius: 50%; /* Make it circular */
    padding: 5px 10px; /* Padding for the badge */
    font-size: 12px; /* Font size */
    font-weight: bold; /* Bold text */
    display: flex;
    align-items: center;
    justify-content: center;
}
    </style>
</head>
<body>


    <!-- Floating Chat Widget -->
    <div class="floating-chat-widget">
        <div class="chat-box" id="chatBox" style="right: 5px;">
            <div class="chat-header">
                <span> <i class="fas fa-comments"></i> Chat with Us</span>
                <i class="fas fa-times" onclick="toggleChatBox()"></i>
            </div>
            <div class="chat-body" id="chatBody">
                <div class="typing-indicator" id="typingIndicator" style="display: none;">
                    <div class="spinner-grow" role="status"></div> Seller is typing...
                </div>
            </div>
            <div class="chat-footer">
                <input type="text" class="form-control" id="chatMessage" placeholder="Type your message" required>
                <button type="button" class="btn" id="sendMessage">
                    <i class="fas fa-paper-plane" style="color: #FFD700;"></i>
                </button>
            </div>
        </div>
        <div class="chat-bubble" onclick="toggleChatBox()">
            <i class="fas fa-comments"></i>
            <span id="unreadCount" class="badge badging"></span>
        </div>
    </div>


<script>
        document.addEventListener("DOMContentLoaded", function() {
            const chatBox = document.getElementById('chatBox');
            chatBox.style.display = 'none'; // Ensure chat box is hidden on load

            document.querySelector('.chat-bubble').onclick = function() {
                toggleChatBox();
            };
            fetchUnreadMessagesCount();
        });

        function toggleChatBox() {
            const chatBox = document.getElementById('chatBox');
            const chatBody = chatBox.querySelector('.chat-body');
            const unreadCountElement = document.getElementById('unreadCount');

            // Toggle visibility of chat box
            if (chatBox.style.display === 'block') {
                chatBox.style.display = 'none'; // Hide chat box
            } else {
                chatBox.style.display = 'block'; // Show chat box
                loadChatMessages(); // Load messages when opening the chat
                markMessagesAsRead(); // Mark order_manager messages as read when opening
                chatBody.classList.add('open'); // Ensure history is visible
            }
              // Clear the unread count badge when the chat is opened
    unreadCountElement.textContent = ''; // Set to empty when clicked
        }

        function markMessagesAsRead() {
    const cust_id = '<?php echo $_SESSION['cust_id']; ?>'; // Assuming cust_id is stored in session
    const unreadCountElement = document.getElementById('unreadCount');

    fetch('../includes/update_is_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ cust_id: cust_id, sender: 'order_manager' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            unreadCountElement.textContent = ''; // Set to empty when clicked
            console.log('Messages marked as read.');
        } else {
            console.error('Failed.');
        }
    })
    .catch(error => console.error('Error updating is_read:', error));
}

function fetchUnreadMessagesCount() {
    fetch('../includes/count_unread_messages.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }
            const unreadCountElement = document.getElementById('unreadCount');
            unreadCountElement.textContent = data.unread_count > 0 ? data.unread_count : '';
        })
        .catch(error => console.error('Error fetching unread messages count:', error));
}



        function loadChatMessages() {
            const chatBody = document.getElementById('chatBody');
            chatBody.innerHTML = ''; // Clear existing messages

            fetch('../includes/fetch_chat_messages.php?cust_id=<?php echo $_SESSION['cust_id']; ?>')
                .then(response => response.json())
                .then(messages => {
                    messages.forEach(msg => {
                        const timestamp = new Date(msg.timestamp).toLocaleString([], {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true // Use 24-hour format, set to true for 12-hour format
                        });
                        const messageClass = msg.sender === 'customer' ? 'customer' : 'seller';
                        chatBody.innerHTML += '<div class="message ' + messageClass + '">' + msg.message + '<div class="timestamp" style="font-size:10px;">' + timestamp + '</div></div>';
                    });
                    chatBody.scrollTop = chatBody.scrollHeight; // Scroll to the bottom
                })
                .catch(error => console.error('Error fetching chat messages:', error));
        }

        document.getElementById('sendMessage').onclick = function() {
            const messageInput = document.getElementById('chatMessage');
            const message = messageInput.value.trim();
            const cust_id = '<?php echo $_SESSION['cust_id']; ?>'; // Assuming cust_id is stored in session

            if (message) {
                // Prepare the data to send
                const data = {
                    cust_id: cust_id,
                    message: message,
                    sender: 'customer'
                };

                // Send the message to the server
                fetch('../includes/send_chat_message.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Display the user's message
                        const chatBody = document.getElementById('chatBody');
                        const timestamp = new Date().toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        chatBody.innerHTML += '<div class="message customer">' + message + '<div class="timestamp">' + timestamp + '</div></div>';

                        // Display the simulated response
                        chatBody.innerHTML += '<div class="message seller">' + data.message + '<div class="timestamp">' + timestamp + '</div></div>';
                        chatBody.scrollTop = chatBody.scrollHeight; // Scroll to the bottom

                        // Clear the input field
                        messageInput.value = '';
                    })
                    .catch(error => console.error('Error sending message:', error));
            }
        };
    </script>
    
</body>
</html>