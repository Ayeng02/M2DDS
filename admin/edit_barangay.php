<?php
include '../includes/db_connect.php';
session_start();

if (isset($_POST['barangay_id']) && isset($_POST['barangay_name']) && isset($_POST['barangay_fee']) && isset($_POST['barangay_route'])) {
    // Get form data
    $barangayId = $_POST['barangay_id'];
    $barangayName = $_POST['barangay_name'];
    $barangayFee = $_POST['barangay_fee'];
    $barangayRoute = $_POST['barangay_route'];

    // Validate that all fields are filled
    if (empty($barangayName) || empty($barangayFee) || empty($barangayRoute)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All fields are required.'
        ]);
        exit();
    }

    // Validate that Barangay Fee is not negative
    if ($barangayFee < 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Barangay Fee cannot be negative.'
        ]);
        exit();
    }

    // Prepare the UPDATE query for Barangay
    $query = "UPDATE brgy_tbl SET Brgy_Name = ?, Brgy_df = ?, brgy_route = ? WHERE Brgy_num = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sdsi", $barangayName, $barangayFee, $barangayRoute, $barangayId);

    if ($stmt->execute()) {
        // Log the update action
        $user_id = $_SESSION['admin_id'];
        $action = "Updated barangay: " . $barangayName;

        $log_stmt = $conn->prepare("INSERT INTO systemlog_tbl (user_id, user_type, systemlog_action, systemlog_date) VALUES (?, ?, ?, NOW())");
        $user_type = 'Admin';

        // Bind and execute the log insert
        $log_stmt->bind_param("sss", $user_id, $user_type, $action);
        $log_stmt->execute();
        $log_stmt->close();

        echo json_encode([
            'status' => 'success',
            'message' => 'Barangay updated successfully.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update Barangay.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters.'
    ]);
}
?>
