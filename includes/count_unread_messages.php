<?php
session_start();

if (!isset($_SESSION['cust_id'])) {
    echo json_encode(['error' => 'Session not set']);
    exit();
}

$cust_id = $_SESSION['cust_id'];

include '../includes/db_connect.php';

// Debugging output
error_log("Customer ID: " . $cust_id); // Logs the cust_id for debugging

$query = "SELECT COUNT(*) as unread_count FROM chat_messages WHERE cust_id = ? AND sender = 'order_manager' AND is_read = 0";
$stmt = $conn->prepare($query);

// Check if cust_id is an integer or string
if (is_numeric($cust_id)) {
    $stmt->bind_param("i", $cust_id); // Use "i" if cust_id is an integer
} else {
    $stmt->bind_param("s", $cust_id); // Use "s" if cust_id is a string
}

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['unread_count' => $row['unread_count']]);

$stmt->close();
$conn->close();
?>
