<?php
include '../includes/db_connect.php';

// Handle the cash input and update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delivery'])) {
    $order_cash = $_POST['order_cash'];
    $order_ids = explode(',', $_POST['order_ids']); // Get order IDs from the hidden input

    // Initialize an array to store error messages
    $error_messages = [];

    // Update order_cash and order_change for all relevant orders
    foreach ($order_ids as $order_id) {
        // Get the order total for the current order
        $order_total_query = "
            SELECT order_total FROM order_tbl 
            WHERE order_id = '$order_id';
        ";

        $total_result = mysqli_query($conn, $order_total_query);
        $order_total_row = mysqli_fetch_assoc($total_result);
        $order_total = $order_total_row['order_total'];

        // Check if order_cash is greater than or equal to order_total
        if ($order_cash < $order_total) {
            $error_messages[] = "Insufficient cash for order ID $order_id. Total amount is $order_total.";
            continue; // Skip this order
        }

        // Calculate change
        $order_change = $order_cash - $order_total;

        // Update query for each order
        $updateOrderQuery = "
            UPDATE order_tbl 
            SET order_cash = '$order_cash', order_change = '$order_change'
            WHERE order_id = '$order_id';
        ";

        // Prepare the update for delivery_transactbl
        $updateTransactQuery = "
            UPDATE delivery_transactbl 
            SET transact_status = 'Success' 
            WHERE order_id = '$order_id' AND shipper_id = '$emp_id';
        ";

        // Execute updates
        mysqli_query($conn, $updateOrderQuery);
        mysqli_query($conn, $updateTransactQuery);
    }

    // Prepare response data
    if (!empty($error_messages)) {
        echo json_encode(['status' => 'error', 'messages' => $error_messages]);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Orders updated successfully!']);
    }

    // Close the database connection
    $conn->close();
    exit;
}
?>
