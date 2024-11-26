<?php
include '../includes/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize inputs
    $barangay_name = mysqli_real_escape_string($conn, $_POST['barangay_name']);
    $barangay_fee = mysqli_real_escape_string($conn, $_POST['barangay_fee']);
    $barangay_route = mysqli_real_escape_string($conn, $_POST['barangay_route']);

    // Check if any field is empty
    if (empty($barangay_name) || empty($barangay_fee) || empty($barangay_route)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All fields are required.'
        ]);
        exit();
    }

    // Check if Barangay Fee is negative
    if ($barangay_fee <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Barangay Fee must be a positive value.'
        ]);
        exit();
    }

    // Check if Barangay Name already exists
    $checkQuery = "SELECT * FROM brgy_tbl WHERE Brgy_Name = '$barangay_name'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Barangay already exists.'
        ]);
        exit();
    }

    // Insert Barangay
    $insertQuery = "INSERT INTO brgy_tbl (Brgy_Name, Brgy_df, brgy_route) 
                    VALUES ('$barangay_name', '$barangay_fee', '$barangay_route')";
    if (mysqli_query($conn, $insertQuery)) {
        // Log the action
        $user_id = $_SESSION['admin_id']; 
        $action = "Added new barangay: " . $barangay_name;

        $log_stmt = $conn->prepare("INSERT INTO systemlog_tbl (user_id, user_type, systemlog_action, systemlog_date) VALUES (?, ?, ?, NOW())");
        $user_type = 'Admin';

        // Bind and execute the log insert
        $log_stmt->bind_param("sss", $user_id, $user_type, $action);
        $log_stmt->execute();
        $log_stmt->close();

        echo json_encode([
            'status' => 'success',
            'message' => 'Barangay added successfully.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add barangay.'
        ]);
    }
}
?>
