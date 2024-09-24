<?php
include '../includes/db_connect.php';

// Get the input data
$data = json_decode(file_get_contents('php://input'), true);

$cust_id = $data['cust_id'];
$message = $data['message'];
$sender = $data['sender'];

// Insert the customer's message into the database
$stmt = $conn->prepare("INSERT INTO chat_messages (cust_id, message, sender) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $cust_id, $message, $sender);
$stmt->execute();

// Check if the order manager has sent a response in the last 30 minutes
$check_time_sql = "SELECT timestamp FROM chat_messages 
                   WHERE cust_id = ? AND sender = 'order_manager' 
                   ORDER BY timestamp DESC LIMIT 1";
$stmt = $conn->prepare($check_time_sql);
$stmt->bind_param("s", $cust_id);
$stmt->execute();
$stmt->bind_result($last_response_timestamp);
$stmt->fetch();
$stmt->close();

// If there's no response from the order manager, or the last response was more than 30 minutes ago
if (!$last_response_timestamp || (strtotime($last_response_timestamp) < strtotime('-30 minutes'))) {
    // Simulate a response from the order manager
    $response_message = "Thank you for your message! We will get back to you shortly.";

    // Optionally, insert the response into the database
    $response_sender = 'order_manager';
    $stmt = $conn->prepare("INSERT INTO chat_messages (cust_id, message, sender) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $cust_id, $response_message, $response_sender);
    $stmt->execute();

    // Close the statement
    $stmt->close();

    // Return a JSON response with the order manager's message
    echo json_encode(['status' => 'success', 'message' => $response_message]);
} else {
    // No response, just acknowledge the receipt of the user's message
    echo json_encode(['status' => 'success', 'message' => 'Message received']);
}

// Close the connection
$conn->close();
?>
