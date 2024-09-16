<?php
session_start();
include '../includes/db_connect.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipper_id = $_POST['shipper_id'];
    $order_ids = $_POST['order_ids'];

    // Debug inputs
    error_log("Shipper ID: " . $shipper_id);
    error_log("Order IDs: " . $order_ids);

    // Ensure session is started and emp_id is available
    if (!isset($_SESSION['emp_id'])) {
        http_response_code(403);
        echo 'Unauthorized';
        error_log("Unauthorized access attempt, emp_id not set in session.");
        exit;
    }

    $om_id = $_SESSION['emp_id']; // Get order manager ID from the session

    // Validate inputs
    if (empty($shipper_id) || empty($order_ids) || empty($om_id)) {
        http_response_code(400);
        echo 'Invalid input';
        error_log("Invalid input: shipper_id or order_ids or om_id is empty.");
        exit;
    }

    // Split order_ids into an array
    $order_ids_array = explode(', ', $order_ids);

    // Debug array of order IDs
    error_log("Order IDs Array: " . print_r($order_ids_array, true));

    // Prepare the SQL call to the stored procedure
    $stmt = $conn->prepare("CALL sp_insertTransaction(?, ?, ?)");
    if (!$stmt) {
        http_response_code(500);
        echo 'Failed to prepare statement';
        error_log("Failed to prepare statement: " . $conn->error);
        exit;
    }

    // Process each order ID
    foreach ($order_ids_array as $order_id) {
        $stmt->bind_param('sss', $shipper_id, $order_id, $om_id);
        if (!$stmt->execute()) {
            $stmt->close();
            $conn->close();
            http_response_code(500);
            echo 'Failed to insert transaction';
            error_log("Failed to insert transaction for order ID: $order_id - " . $stmt->error);
            exit;
        }
    }

    $stmt->close();

    // Update status of each order to 'Shipped'
    $update_stmt = $conn->prepare("UPDATE order_tbl SET status_code = 3 WHERE order_id = ?");
    if (!$update_stmt) {
        http_response_code(500);
        echo 'Failed to prepare update statement';
        error_log("Failed to prepare order update statement: " . $conn->error);
        exit;
    }

    foreach ($order_ids_array as $order_id) {
        $update_stmt->bind_param('s', $order_id);
        if (!$update_stmt->execute()) {
            $update_stmt->close();
            $conn->close();
            http_response_code(500);
            echo 'Failed to update order status';
            error_log("Failed to update order status for order ID: $order_id - " . $update_stmt->error);
            exit;
        }
    }

    $update_stmt->close();

    // Update the status of the shipper to 'On Shipped'
    $update_shipper_stmt = $conn->prepare("UPDATE emp_tbl SET emp_status = 'On Shipped' WHERE emp_id = ?");
    if (!$update_shipper_stmt) {
        http_response_code(500);
        echo 'Failed to prepare shipper update statement';
        error_log("Failed to prepare shipper update statement: " . $conn->error);
        exit;
    }

    $update_shipper_stmt->bind_param('s', $shipper_id);
    if (!$update_shipper_stmt->execute()) {
        $update_shipper_stmt->close();
        $conn->close();
        http_response_code(500);
        echo 'Failed to update shipper status';
        error_log("Failed to update shipper status for emp_id: $shipper_id - " . $update_shipper_stmt->error);
        exit;
    }

    $update_shipper_stmt->close();
    $conn->close();

    echo 'Success';
    error_log("Transaction and updates completed successfully.");
}
