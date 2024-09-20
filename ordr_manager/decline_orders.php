<?php
// Suppress error display but log errors
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL); // Log all errors

// Ensure JSON output
header('Content-Type: application/json');

include '../includes/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$orders = $data['orders'];
$reason = $data['reason'];
$success = true;
$errorMessages = [];

foreach ($orders as $order) {
    $order_id = $order['order_id']; // String type order_id like ORD2024AUG00

    // SQL to update order status (to 'declined')
    $stmt = $conn->prepare("UPDATE order_tbl SET status_code = 7 WHERE order_id = ?");
    if (!$stmt) {
        $errorMessages[] = "Error preparing statement for order #$order_id: " . $conn->error;
        $success = false;
        continue;
    }
    // Bind parameter: order_id (string)
    $stmt->bind_param('s', $order_id);

    if (!$stmt->execute()) {
        $errorMessages[] = "Error executing statement for order #$order_id: " . $stmt->error;
        $success = false;
    }

    $stmt->close();

    // Fetch customer ID from order_tbl to insert a notification
    $stmt = $conn->prepare("SELECT cust_id FROM order_tbl WHERE order_id = ?");
    if (!$stmt) {
        $errorMessages[] = "Error preparing statement for fetching customer ID for order #$order_id: " . $conn->error;
        $success = false;
        continue;
    }

    // Bind the order_id (string)
    $stmt->bind_param('s', $order_id);
    $stmt->execute();
    $stmt->bind_result($cust_id);
    $stmt->fetch();
    $stmt->close();

    // Insert notification into notifications table
    $message = "Your order #$order_id has been declined. Reason: $reason.";
    $stmt = $conn->prepare("INSERT INTO notifications (cust_id, message) VALUES (?, ?)");
    if (!$stmt) {
        $errorMessages[] = "Error preparing notification insert for order #$order_id: " . $conn->error;
        $success = false;
        continue;
    }

    // Bind the customer ID and the message (both strings)
    $stmt->bind_param('ss', $cust_id, $message);

    if (!$stmt->execute()) {
        $errorMessages[] = "Error inserting notification for order #$order_id: " . $stmt->error;
        $success = false;
    }

    $stmt->close();
}

$conn->close();

// Return response as JSON
if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => implode(', ', $errorMessages)]);
}
?>
