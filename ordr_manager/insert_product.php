<?php
// insert_product.php

// Database connection
include '../includes/db_connect.php';
session_start(); // Start session to access emp_id

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get product details from the AJAX request
    $category_code = $_POST['category_code'];
    $prod_name = $_POST['prod_name'];
    $prod_desc = $_POST['prod_desc'];
    $prod_price = $_POST['prod_price'];
    $prod_discount = $_POST['prod_discount'];
    $prod_qoh = $_POST['prod_qoh'];
    $prod_img = $_FILES['prod_img'];
    $emp_id = $_SESSION['emp_id']; // Get emp_id from session

    // Define the upload directory
    $upload_dir = '../Product-Images/';
    $target_file = $upload_dir . basename($prod_img['name']);
    $relative_path_to_store = "Product-Images/" . basename($prod_img['name']);

    // Validate file upload
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if the image file is an actual image or fake image
    $check = getimagesize($prod_img['tmp_name']);
    if ($check === false) {
        echo 'Not an image';
        exit;
    }

    // Check file size (maximum 5MB)
    if ($prod_img['size'] > 5 * 1024 * 1024) {
        echo 'File is too large';
        exit;
    }

    // Allow certain file formats (PNG, JPG, JPEG)
    if ($imageFileType != 'jpg' && $imageFileType != 'jpeg' && $imageFileType != 'png') {
        echo 'Wrong file format';
        exit;
    }

    // Attempt to move the uploaded file to the target directory
    if (!move_uploaded_file($prod_img['tmp_name'], $target_file)) {
        echo 'Failed to move uploaded file';
        exit;
    }

    // Prepare the stored procedure call
    $stmt = $conn->prepare("CALL sp_InsertProduct(?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo 'Prepare failed: ' . $conn->error; // Display error if prepare fails
        exit;
    }

    $stmt->bind_param("sssddds", $category_code, $prod_name, $prod_desc, $prod_price, $prod_discount, $prod_qoh, $relative_path_to_store);

    // Execute the stored procedure
    if ($stmt->execute()) {
        // Log the action in the system log
        $action = "Added new product: " . $prod_name;
        $logStmt = $conn->prepare("INSERT INTO systemlog_tbl (user_id, user_type, systemlog_action, systemlog_date) VALUES (?, 'Employee', ?, NOW())");
        $logStmt->bind_param("ss", $emp_id, $action);

        if ($logStmt->execute()) {
            echo 'success'; // Indicate success for both product addition and logging
        } else {
            echo 'Product added, but logging action failed';
        }
        $logStmt->close();
    } else {
        echo 'Execution failed: ' . $stmt->error; // Display execution error
    }

    // Close statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
