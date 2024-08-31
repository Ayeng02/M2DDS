<?php
session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Include database connection
include 'db_connect.php'; // Adjust the path if needed

// Check if customer_id is set in session
if (!isset($_SESSION['cust_id'])) {
    echo json_encode(['success' => false, 'message' => 'Customer ID not set in session']);
    exit;
}

// Check if notification ID is provided
if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Notification ID not provided']);
    exit;
}

$notificationId = $_POST['id'];
$customer_id = $_SESSION['cust_id'];

// Update notification status to 'read'
$sql = "UPDATE notifications SET status = 'read' WHERE id = ? AND cust_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $notificationId, $customer_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update notification']);
}

$stmt->close();
$conn->close();
?>
