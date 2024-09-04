<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meat-To-Door Delivery System</title>
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .floating-chat-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        .chat-box {
            display: none;
            position: fixed;
            bottom: 60px;
            right: 20px;
            width: 300px;
            height: 400px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background: #fff;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            background: #800000;
            /* Maroon */
            color: #FFD700;
            /* Semi-Light Gold */
            padding: 15px;
            border-radius: 10px 10px 0 0;
            cursor: pointer;
            text-align: center;
            font-weight: bold;
        }

        .chat-body {
            padding: 15px;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .chat-footer {
            padding: 10px;
            border-top: 1px solid #ddd;
            background: #f8f9fa;
            display: flex;
        }

        .chat-footer input {
            flex: 1;
            border-radius: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            margin-right: 10px;
        }

        .chat-footer button {
            border-radius: 20px;
            background: #800000;
            /* Maroon */
            color: #FFD700;
            /* Semi-Light Gold */
            border: none;
        }

        .chat-footer button:hover {
            background: #6f0000;
            /* Darker Maroon */
        }

        .message {
            margin-bottom: 10px;
            max-width: 80%;
        }

        .message.user {
            align-self: flex-end;
            background: #800000;
            /* Maroon */
            color: #fff;
            padding: 10px;
            border-radius: 20px 20px 0 20px;
        }

        .message.seller {
            align-self: flex-start;
            background: #f5f5f5;
            color: #333;
            padding: 10px;
            border-radius: 20px 20px 20px 0;
        }
    </style>
</head>

<body>
    <!-- Your content here -->

    <!-- Floating Chat Widget -->
    <div class="floating-chat-widget">
        <div class="chat-box" id="chatBox">
            <div class="chat-header" onclick="toggleChatBox()">
                Chat with Us
            </div>
            <div class="chat-body" id="chatBody">
                <!-- Chat messages will appear here -->
            </div>
            <div class="chat-footer">
                <input type="text" class="form-control" id="chatMessage" placeholder="Type your message" required>
                <button type="button" class="btn btn-primary" id="sendMessage">Send</button>
            </div>
        </div>
        <button type="button" class="btn btn-primary" onclick="toggleChatBox()">
            <i class="bi bi-chat-dots"></i> Chat
        </button>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleChatBox() {
            const chatBox = document.getElementById('chatBox');
            chatBox.style.display = chatBox.style.display === 'none' || chatBox.style.display === '' ? 'flex' : 'none';
        }

        document.getElementById('sendMessage').addEventListener('click', function() {
            const message = document.getElementById('chatMessage').value;
            const chatBody = document.getElementById('chatBody');
            if (message.trim()) {
                // Add user message
                chatBody.innerHTML += `<div class="message user">${message}</div>`;
                document.getElementById('chatMessage').value = '';
                chatBody.scrollTop = chatBody.scrollHeight; // Scroll to the bottom

                // Simulate seller response
                setTimeout(() => {
                    chatBody.innerHTML += `<div class="message seller">Thank you for your message! We will get back to you shortly.</div>`;
                    chatBody.scrollTop = chatBody.scrollHeight;
                }, 1000);
            }
        });
    </script>
</body>

</html>