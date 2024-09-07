<?php
// Start the session
session_start();

// Include your database connection script
include '../includes/db_connect.php';

// Function to validate phone number
function validatePhoneNumber($phone) {
    return preg_match('/^(09\d{9})$/', $phone);
}

// Function to validate address
function validateAddress($address) {
    return preg_match('/^[\w\s\.,\-#]+, [\w\s]+, [\w\s]+$/', $address);
}

// Function to check username format
function validateUsername($username) {
    return preg_match('/^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z\d]{6,}$/', $username);
}

// Check if the user is logged in
if (!isset($_SESSION['cust_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

// Retrieve data from POST request
$f_name = $_POST['f_name'];
$l_name = $_POST['l_name'];
$username = $_POST['username'];
$address = $_POST['add_ress'];
$email = $_POST['email'];
$phone_num = $_POST['phone_num'];
$cust_id = $_SESSION['cust_id'];

// Validate fields
if (!validatePhoneNumber($phone_num) || !validateAddress($address) || !validateUsername($username)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    exit();
}

// Check for existing email and username
$sql = "SELECT COUNT(*) FROM customers WHERE (email = ? OR username = ?) AND cust_id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $email, $username, $cust_id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    echo json_encode(['success' => false, 'message' => 'Email or Username already exists.']);
    exit();
}

// Update the customer's information
$sql = "UPDATE customers SET f_name = ?, l_name = ?, username = ?, address = ?, email = ?, phone_num = ? WHERE cust_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sssssss', $f_name, $l_name, $username, $address, $email, $phone_num, $cust_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
}
$stmt->close();
?>
