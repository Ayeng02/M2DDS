<?php
session_start();
include '../includes/db_connect.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipper_id = $_POST['shipper_id'];
    $order_ids = $_POST['order_ids'];

    if (!isset($_SESSION['emp_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $om_id = $_SESSION['emp_id']; // Get the order manager ID from the session

    if (empty($shipper_id) || empty($order_ids) || empty($om_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input. Please provide all necessary information.']);
        exit;
    }

    $order_ids_array = explode(', ', $order_ids);

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("CALL sp_insertTransaction(?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Failed to prepare the transaction statement: ' . $conn->error);
        }

        foreach ($order_ids_array as $order_id) {
            $stmt->bind_param('sss', $shipper_id, $order_id, $om_id);
            if (!$stmt->execute()) {
                throw new Exception('Failed to insert transaction for order: ' . $order_id . '. Error: ' . $stmt->error);
            }
        }
        $stmt->close();

        $update_stmt = $conn->prepare("UPDATE order_tbl SET status_code = 3 WHERE order_id = ?");
        if (!$update_stmt) {
            throw new Exception('Failed to prepare the order status update statement: ' . $conn->error);
        }

        foreach ($order_ids_array as $order_id) {
            $update_stmt->bind_param('s', $order_id);
            if (!$update_stmt->execute()) {
                throw new Exception('Failed to update order status for order: ' . $order_id . '. Error: ' . $update_stmt->error);
            }
        }
        $update_stmt->close();

        $update_shipper_stmt = $conn->prepare("UPDATE emp_tbl SET emp_status = 'On Shipped' WHERE emp_id = ?");
        if (!$update_shipper_stmt) {
            throw new Exception('Failed to prepare the shipper status update statement: ' . $conn->error);
        }

        $update_shipper_stmt->bind_param('s', $shipper_id);
        if (!$update_shipper_stmt->execute()) {
            throw new Exception('Failed to update shipper status. Error: ' . $update_shipper_stmt->error);
        }
        $update_shipper_stmt->close();

        $conn->commit();
        $conn->close();

        echo json_encode(['success' => 'Transaction and status update completed successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
}
?>
