<?php
include '../includes/db_connect.php'; 

// Get POST data
$prod_code = $_POST['prod_code'];
$add_quantity = $_POST['add_quantity'];

// Update the quantity in the product_tbl (adding to the existing quantity)
$sql = "UPDATE product_tbl SET prod_qoh = prod_qoh + ? WHERE prod_code = ?";
$stmt = $conn->prepare($sql); // Use $conn instead of $mysqli
$stmt->bind_param("ds", $add_quantity, $prod_code);

if ($stmt->execute()) {
    echo 'success'; // Return success message
} else {
    echo 'error'; // Return error message
}

$stmt->close();
$conn->close(); // Ensure you're closing the $conn variable
?>
