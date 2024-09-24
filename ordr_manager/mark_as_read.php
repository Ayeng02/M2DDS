<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cust_id = $_POST['cust_id'];

    // Update all unread messages from the customer to mark them as read
    $sql = "UPDATE chat_messages 
            SET is_read = 1 
            WHERE cust_id = ? AND sender = 'customer' AND is_read = 0";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cust_id);
    $stmt->execute();

    // Fetch the chat messages for the specified customer
    $messagesSql = "SELECT * FROM chat_messages WHERE cust_id = ? ORDER BY timestamp ASC";
    $stmt = $conn->prepare($messagesSql);
    $stmt->bind_param("s", $cust_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Output messages in HTML format
    while ($message = $result->fetch_assoc()) {
        echo "<div class='" . ($message['sender'] == 'customer' ? 'message-user' : 'message-admin') . "'>";
        echo htmlspecialchars($message['message']);
        echo "<div class='message-timestamp'>" . $message['timestamp'] . "</div>";
        echo "</div>";
    }

    $stmt->close();
}
$conn->close();
?>
