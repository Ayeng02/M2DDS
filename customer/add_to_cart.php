<?php
session_start();
include '../includes/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['cust_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);

    exit();
}

// Get POST data
$prod_code = $_POST['prod_code'];
$cart_qty = $_POST['cart_qty'];
$cust_id = $_SESSION['cust_id'];

// Validate input
if (empty($prod_code) || !is_numeric($cart_qty) || $cart_qty <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit();
}

// Check if the product is already in the cart
$sql_check = "SELECT cart_qty FROM cart_table WHERE cust_id = ? AND prod_code = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('ss', $cust_id, $prod_code);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Product exists in the cart, update the quantity
    $row = $result_check->fetch_assoc();
    $existing_qty = $row['cart_qty'];
    $new_qty = $existing_qty + $cart_qty;

    $sql_update = "UPDATE cart_table SET cart_qty = ? WHERE cust_id = ? AND prod_code = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param('dss', $new_qty, $cust_id, $prod_code);

    if ($stmt_update->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Product quantity updated in cart']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update product quantity in cart']);
    }
    $stmt_update->close();
} else {
    // Product does not exist in the cart, insert new entry
    $sql_insert = "INSERT INTO cart_table (cust_id, prod_code, cart_qty) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param('ssd', $cust_id, $prod_code, $cart_qty);

    if ($stmt_insert->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Product added to cart']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add product to cart']);
    }
    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();
