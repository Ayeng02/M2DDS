<?php
include '../includes/db_connect.php';

// Fetch distinct cust_id, customer names, count of unread messages, and order by newest message
$sql = "SELECT cm.cust_id, 
               CONCAT(c.f_name, ' ', c.l_name) AS customer, 
               SUM(CASE WHEN cm.is_read = 0 AND cm.sender = 'customer' THEN 1 ELSE 0 END) AS unread_count,
               MAX(cm.timestamp) AS last_message_time  -- Get the latest message timestamp for each cust_id
        FROM chat_messages cm
        JOIN customers c ON cm.cust_id = c.cust_id
        GROUP BY cm.cust_id
        ORDER BY last_message_time DESC";  // Order by the newest message timestamp

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer | Order Manager</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/ordr_css.css">
    <link rel="stylesheet" href="../css/chat_om.css">

    <style>
        /* Sidebar active link */
        .active4 {
            background: linear-gradient(180deg, #ff83259b, #a72828);
        }


    </style>
</head>

<body>
    <!-- Sidebar on the left -->
    <?php include '../includes/omSideBar.php'; ?>

    <div class="content">
        <!-- Real-time clock -->
        <div id="clock-container" class="mb-3">
            <div id="clock"></div>
            <div id="date"></div>
        </div>

        <div class="row">
            <!-- Left Side - Customer List -->
            <div class="col-md-4" style="margin-bottom: 10px;">
    <div class="widget-container">
        <h5 class="widget-title">Customers</h5>
        <div class="customer-list">
            <div class="search-container">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by Name...">
                <button id="searchButton" class="btn btn-secondary">Search</button>
            </div>
            <div id="customerList"> <!-- This div will hold customer items -->
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $cust_id = htmlspecialchars($row['cust_id']); // Sanitize output
                        $customerName = htmlspecialchars($row['customer']); // Sanitize customer name
                        $unreadCount = $row['unread_count']; // Get unread message count

                        // Conditionally display the badge if there are unread messages
                        $badge = ($unreadCount > 0) ? "<span class='badge badge-danger'>$unreadCount</span>" : "";

                        echo "<div class='customer-item' data-cust-id='$cust_id' onclick=\"loadCustomerChat('$customerName', '$cust_id', this)\">
                                    <i class='fa fa-user-circle' style='color: #a72828;'></i>
                                    <span>$customerName $badge</span>
                              </div>";
                    }
                } else {
                    echo "<div class='text-center'>No customers messages found.</div>";
                }
                $conn->close(); // Close the database connection
                ?>
            </div>
        </div>
    </div>
</div>
            <!-- Right Side - Chat Messaging -->
            <div class="col-md-8">
                <div class="widget-container">
                    <h5 class="widget-title" id="customerName">Select a Customer to Chat</h5>
                    <div id="message-box" class="message-box">
                        <!-- Placeholder for messages -->
                    </div>

                    <div class="chat-input">
                        <textarea id="messageInput" class="form-control" rows="2" placeholder="Type your message..."></textarea>
                        <button id="sendMessage" class="btn btn-primary sendBtn" style="color:#ffffff; border:none;">Send</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/order_manager.js"></script>


    <script>
$(document).ready(function() {
    // Search for customers and display the results
    $('#searchButton').click(function() {
        const searchTerm = $('#searchInput').val().trim();
        
        // Prevent sending an empty search term
        if (searchTerm.length > 0) {
            $.ajax({
                url: 'search_customers.php', // Ensure this is the correct path
                type: 'GET',
                data: { term: searchTerm },
                success: function(data) {
                    $('#customerList').html(data); // Update customer list with search results
                },
                error: function() {
                    $('#customerList').html('<div>An error occurred while searching.</div>');
                }
            });
        } else {
            alert("Please enter a search term.");
        }
    });

    // After clicking on a customer from the search results, load their chat messages
    $(document).on('click', '.customer-item', function() {
        const customerName = $(this).find('span').text();  // Get customer name
        const custId = $(this).data('cust-id');  // Get customer ID

        loadCustomerChat(customerName, custId, this);  // Load chat for selected customer
    });
});

// Function to load the chat for the selected customer
function loadCustomerChat(customerName, custId, element) {
    document.getElementById('customerName').textContent = 'Chat with ' + customerName;

    // Remove active class from all customers and add it to the selected one
    $('.customer-item').removeClass('active');
    $(element).addClass('active');

    // Clear previous chat history
    $('#message-box').html(''); 

    // Fetch chat messages for the selected customer
    $.ajax({
        url: 'fetch_chat_messages.php', // Ensure this is the correct path
        type: 'GET',
        data: { cust_id: custId },
        success: function(data) {
            if (data.trim()) { // If there are messages
                $('#message-box').html(data); // Load chat history
                $('#message-box').scrollTop($('#message-box')[0].scrollHeight); // Auto-scroll to bottom
            } else {
                // If no messages, inform the user and allow sending a new message
                $('#message-box').html('<div class="no-messages">No previous messages. Start a new conversation.</div>');
            }
        },
        error: function() {
            $('#message-box').html('<div>An error occurred while loading chat messages.</div>');
        }
    });

    // Mark messages as read once the chat is loaded
    $.ajax({
        url: 'mark_as_read.php', // PHP script to mark messages as read
        type: 'POST',
        data: { cust_id: custId },
        success: function() {
            $(element).find('.badge').remove();  // Remove unread badge for the selected customer
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error marking messages as read: " + textStatus, errorThrown);
        }
    });
}


$(document).ready(function() {
    const messageBox = $('#message-box');
    const messageInput = $('#messageInput');

    $('#sendMessage').click(function() {
        const message = messageInput.val().trim();
        const custId = $('.customer-item.active').data('cust-id'); // Get the currently active customer ID

        // Debugging: Check if the customer ID and message are being retrieved
        console.log('Customer ID:', custId);
        console.log('Message:', message);

        if (message.length > 0 && custId) {
            // Append user message to the chat box
            messageBox.append('<div class="message-admin">' + message + '</div>');
            messageBox.append('<div class="message-timestamp">[Sending...]</div>'); // Temporary message

            // Clear input
            messageInput.val('');

            // Send message to the server
            $.ajax({
                url: 'send_message.php', // Ensure the path to this file is correct
                type: 'POST',
                data: {
                    cust_id: custId,
                    message: message,
                    sender: 'order_manager' // Assuming the sender is the order manager
                },
                success: function(response) {
                    // Debugging: Check what response is returned from the server
                    console.log('Server Response:', response);

                    // Update message box with the sent message status
                    messageBox.find('.message-timestamp').last().text('[Sent]');
                    messageBox.scrollTop(messageBox[0].scrollHeight); // Auto-scroll to bottom
                },
                error: function(xhr, status, error) {
                    // Debugging: Check the error details
                    console.error('AJAX Error:', status, error);

                    messageBox.append('<div class="message-admin">An error occurred while sending the message.</div>');
                }
            });
        } else {
            if (!message.length) {
                alert("Please enter a message.");
            }
        }
    });
});


function updateClock() {
    const now = new Date();

    // Format hours, minutes, and seconds
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    // Determine AM/PM
    const ampm = hours >= 12 ? 'PM' : 'AM';

    // Convert hours from 24-hour to 12-hour format
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'

    // Format time
    const timeString = `${String(hours).padStart(2, '0')}:${minutes}:${seconds} ${ampm}`;

    // Update the clock and date elements
    document.getElementById('clock').textContent = timeString;

    // Format the date
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const day = days[now.getDay()];
    const date = now.toLocaleDateString('en-US', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
    document.getElementById('date').textContent = `${day}, ${date}`;
}

setInterval(updateClock, 1000); // Update the clock every second
updateClock(); // Initial call to display the time immediately

    </script>
</body>

</html>