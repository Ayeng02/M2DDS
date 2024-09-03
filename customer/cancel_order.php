<?php
session_start();
include '../includes/db_connect.php';

$response = ['success' => false, 'error' => 'An unknown error occurred.'];

if (isset($_POST['order_id']) && isset($_SESSION['cust_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $cust_id = $_SESSION['cust_id'];

    // Update status to 'Canceled'
    $query = "
        UPDATE Order_tbl
        SET status_code = (SELECT status_code FROM status_tbl WHERE status_name = 'Canceled')
        WHERE order_id = '$order_id' AND cust_id = '$cust_id' AND status_code IN (
            SELECT status_code FROM status_tbl WHERE status_name IN ('Pending', 'Processing')
        )
    ";

    if (mysqli_query($conn, $query)) {
        $response['success'] = true;
    } else {
        $response['error'] = 'Failed to update the order status.';
    }
}

echo json_encode($response);
