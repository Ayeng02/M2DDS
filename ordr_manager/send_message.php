<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure that cust_id and message are provided
    if (isset($_POST['cust_id']) && isset($_POST['message']) && isset($_POST['sender'])) {
        $cust_id = $conn->real_escape_string($_POST['cust_id']);
        $message = $conn->real_escape_string($_POST['message']);
        $sender = $conn->real_escape_string($_POST['sender']);

        // Insert the message into the database
        $sql = "INSERT INTO chat_messages (cust_id, sender, message, timestamp) VALUES ('$cust_id', '$sender', '$message', NOW())";

        if ($conn->query($sql) === TRUE) {
            echo "Message sent at " . date('F j, Y h:i A'); // Return confirmation message
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Missing parameters.";
    }
}

$conn->close();
?>
