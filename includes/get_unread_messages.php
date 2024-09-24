<?php
include '../includes/db_connect.php';

// Query to count unread messages from customers
$query = "SELECT COUNT(*) AS unread_count FROM chat_messages WHERE is_read = 0 AND sender = 'customer'";
$result = $conn->query($query);
$row = $result->fetch_assoc();

// Return the unread message count as a JSON response
echo json_encode(['unread_count' => $row['unread_count']]);

// Close the database connection
$conn->close();
?>
