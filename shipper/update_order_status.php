<?php
// Include database connection
include('../includes/db_connect.php');

session_start();

// Get the emp_id from the session
$emp_id = $_SESSION['emp_id']; // Assuming emp_id is stored in session

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_cash'])) {
    // Retrieve and validate input
    $order_cash = $_POST['order_cash'];
    $order_ids = explode(',', $_POST['order_ids']); // Get order IDs from the hidden input

    // Check if cash amount is valid
    if (!is_numeric($order_cash) || floatval($order_cash) <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid cash amount']);
        exit;
    }

    // Initialize an array to store the SQL updates for transactions
    $updateQueries = [];
    $allUpdatesSuccessful = true;

    // Update order_cash and order_change for all relevant orders
    foreach ($order_ids as $order_id) {
        // Get the order total for the current order
        $order_total_query = "
            SELECT order_total FROM order_tbl 
            WHERE order_id = '$order_id';
        ";

        $total_result = mysqli_query($conn, $order_total_query);
        if (!$total_result) {
            echo json_encode(['status' => 'error', 'message' => 'Database query error']);
            exit;
        }

        $order_total_row = mysqli_fetch_assoc($total_result);
        if (!$order_total_row) {
            $allUpdatesSuccessful = false;
            continue; // Skip if the order doesn't exist
        }

        $order_total = $order_total_row['order_total'];

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

        // Execute updates and check for errors
        if (!mysqli_query($conn, $updateOrderQuery) || !mysqli_query($conn, $updateTransactQuery)) {
            $allUpdatesSuccessful = false;
            break; // Exit loop on failure
        }

         // Insert log into emplog_tbl with status 4
         $logQuery = "
         INSERT INTO emplog_tbl (emp_id, order_id, emplog_status, emplog_action, emplog_date) 
         VALUES ('$emp_id', '$order_id', 4, 'Delivered Order', NOW());
         ";
        
          // Execute updates and check for errors
        if (!mysqli_query($conn,  $logQuery)) {
            $allUpdatesSuccessful = false;
            break; // Exit loop on failure
        }

    }

    // Return a success response or an error response
    if ($allUpdatesSuccessful) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Some updates failed']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
