<?php 
// Include database connection
include('../includes/db_connect.php');

session_start();

// Get the emp_id from the session
$emp_id = $_SESSION['emp_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if order_ids and status are set
    if (isset($_POST['order_ids']) && isset($_POST['status'])) {
        $order_ids = explode(',', $_POST['order_ids']); // Get order IDs from the POST data
        $status = mysqli_real_escape_string($conn, $_POST['status']); // Escape the status

        $response = ['status' => 'error', 'message' => 'Failed to update orders.'];

        // Prepare the update query
        foreach ($order_ids as $order_id) {
            $order_id = mysqli_real_escape_string($conn, trim($order_id)); // Sanitize input

            // Prepare the update query
            $updateTransactQuery = "
                UPDATE delivery_transactbl 
                SET transact_status = '$status' 
                WHERE order_id = '$order_id' AND shipper_id = '$emp_id';
            ";

            // Execute the update
            if (mysqli_query($conn, $updateTransactQuery)) {
                $response['status'] = 'success'; // Update the response status to success

                  // Insert log entry into emplog_tbl with status 6
                  $logQuery = "
                  INSERT INTO emplog_tbl (emp_id, order_id, emplog_status, emplog_action, emplog_date) 
                  VALUES ('$emp_id', '$order_id', 6, 'Update to Failed Pick-Up Order', NOW());
                  ";

              // Execute log insert and check for errors
              if (!mysqli_query($conn, $logQuery)) {
                  $response['message'] = 'Failed to log action for order #' . $order_id;
                  break; // Exit the loop on logging failure
              }

            } else {
                // If an error occurs, capture the error message
                $response['message'] = mysqli_error($conn);
                break; // Exit the loop on first failure
            }
        }

        echo json_encode($response); // Return JSON response
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
