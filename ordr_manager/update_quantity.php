<?php
include '../includes/db_connect.php'; 
session_start(); // Start session to access emp_id

// Get POST data
$prod_code = $_POST['prod_code'];
$add_quantity = $_POST['add_quantity'];
$emp_id = $_SESSION['emp_id']; // Get emp_id from session

// Update the quantity in the product_tbl (adding to the existing quantity)
$sql = "UPDATE product_tbl SET prod_qoh = prod_qoh + ? WHERE prod_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ds", $add_quantity, $prod_code);

if ($stmt->execute()) {
    // Log the action in the system log
    $action = "Added quantity (" . $add_quantity . ") to product code: " . $prod_code;
    $logStmt = $conn->prepare("INSERT INTO systemlog_tbl (user_id, user_type, systemlog_action, systemlog_date) VALUES (?, 'Employee', ?, NOW())");
    $logStmt->bind_param("ss", $emp_id, $action);

    if ($logStmt->execute()) {
        echo 'success'; // Indicate success for both quantity addition and logging
    } else {
        echo 'Quantity added, but logging action failed';
    }
    $logStmt->close();
} else {
    echo 'error'; // Return error message if update fails
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
