<?php
session_start();
include '../includes/db_connect.php';

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Check if the user is logged in
    if (!isset($_SESSION['cust_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }

    $cust_id = $_SESSION['cust_id'];

    // Check if the cart is empty before attempting to remove items
    $check_sql = "SELECT COUNT(*) as item_count FROM cart_table WHERE cust_id = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param('s', $cust_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $cart = $result_check->fetch_assoc();
    
    if ($cart['item_count'] == 0) {
        echo json_encode(['success' => false, 'message' => 'Cart is already empty']);
        exit();
    }

    // Prepare and execute the SQL statement to remove all items from the cart
    $sql = "DELETE FROM cart_table WHERE cust_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $cust_id);
    $success = $stmt->execute();

    if (!$success) {
        throw new Exception('Failed to execute the query.');
    }

    $stmt->close();
    $conn->close();

    // Return success response
    echo json_encode(['success' => true, 'message' => 'All items have been removed from the cart']);
} catch (Exception $e) {
    // Return error response
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
