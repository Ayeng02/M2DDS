<?php
session_start();
include '../includes/db_connect.php';

$cust_id = $_SESSION['cust_id']; // Use session variable
$sql = "SELECT * FROM chat_messages WHERE cust_id = ? ORDER BY timestamp ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $cust_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'message' => $row['message'],
        'sender' => $row['sender'],
        'timestamp' => $row['timestamp']
    ];
}

header('Content-Type: application/json');
echo json_encode($messages);

$stmt->close();
$conn->close();
?>
