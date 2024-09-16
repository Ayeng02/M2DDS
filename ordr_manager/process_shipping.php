<?php
session_start();
include '../includes/db_connect.php'; // Include your database connection

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipper_id = $_POST['shipper_id'];
    $order_ids = $_POST['order_ids'];

    // Ensure session is started and emp_id is available
    if (!isset($_SESSION['emp_id'])) {
        http_response_code(403);
        echo 'Unauthorized';
        exit;
    }

    $om_id = $_SESSION['emp_id']; // Get order manager ID from the session

    // Validate inputs
    if (empty($shipper_id) || empty($order_ids) || empty($om_id)) {
        http_response_code(400);
        echo 'Invalid input';
        exit;
    }

    // Split order_ids into an array
    $order_ids_array = explode(', ', $order_ids);

    // Prepare the SQL call to the stored procedure
    if (!$stmt = $conn->prepare("CALL sp_insertTransaction(?, ?, ?)")) {
        error_log("Failed to prepare statement: " . $conn->error);
        http_response_code(500);
        echo 'Failed to prepare statement';
        exit;
    }

    // Process each order ID
    foreach ($order_ids_array as $order_id) {
        $stmt->bind_param('sss', $shipper_id, $order_id, $om_id);
        if (!$stmt->execute()) {
            error_log("Failed to execute statement: " . $stmt->error);
            $stmt->close();
            $conn->close();
            http_response_code(500);
            echo 'Failed to insert transaction';
            exit;
        }
    }

    $stmt->close();

    // Update status of each order to 'Shipped'
    if (!$update_stmt = $conn->prepare("UPDATE order_tbl SET status_code = 3 WHERE order_id = ?")) {
        error_log("Failed to prepare update statement: " . $conn->error);
        http_response_code(500);
        echo 'Failed to prepare update statement';
        exit;
    }

    foreach ($order_ids_array as $order_id) {
        $update_stmt->bind_param('s', $order_id);
        if (!$update_stmt->execute()) {
            error_log("Failed to update order status: " . $update_stmt->error);
            $update_stmt->close();
            $conn->close();
            http_response_code(500);
            echo 'Failed to update order status';
            exit;
        }
    }

    $update_stmt->close();

    // Update the status of the shipper to 'On Shipped'
    if (!$update_shipper_stmt = $conn->prepare("UPDATE emp_tbl SET emp_status = 'On Shipped' WHERE emp_id = ?")) {
        error_log("Failed to prepare shipper update statement: " . $conn->error);
        http_response_code(500);
        echo 'Failed to prepare shipper update statement';
        exit;
    }

    $update_shipper_stmt->bind_param('s', $shipper_id);
    if (!$update_shipper_stmt->execute()) {
        error_log("Failed to update shipper status: " . $update_shipper_stmt->error);
        $update_shipper_stmt->close();
        $conn->close();
        http_response_code(500);
        echo 'Failed to update shipper status';
        exit;
    }

    $update_shipper_stmt->close();
    $conn->close();

    echo 'Success';
}
?>
