<?php
// Include database connection
session_start();
include '../includes/db_connect.php';

// Check if the id parameter is set
if (isset($_GET['id'])) {
    // Get the order ID from the URL
    $order_id = $_GET['id'];

    // Prepare the SQL statement to delete the order
    $sql = "DELETE FROM order_tbl WHERE order_id = ?"; // Adjust the table name if needed

    // Prepare and execute the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameter
        $stmt->bind_param('s', $order_id);

        // Attempt to execute the statement
        if ($stmt->execute()) {
            // Successfully deleted, set success message
            $_SESSION['delete_success'] = 'Order deleted successfully!';
        } else {
            // Error occurred, set error message
            $_SESSION['delete_error'] = 'Error deleting order: ' . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        // Error preparing SQL statement
        $_SESSION['error'] = 'Error preparing SQL statement: ' . $conn->error;
    }
} else {
    $_SESSION['error'] = 'No order ID specified.';
}

// Close the database connection
$conn->close();

// Redirect back to the orders list page (or wherever you want)
header("Location: viewOrders.php");
exit();
?>
