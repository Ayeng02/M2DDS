<?php
include '../includes/db_connect.php';
session_start();

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // Prepare DELETE query for Barangay
    $deleteQuery = "DELETE FROM brgy_tbl WHERE Brgy_num = '$id'";

    if (mysqli_query($conn, $deleteQuery)) {
        // Log the deletion action
        $user_id = $_SESSION['admin_id']; 
        $action = "Deleted barangay with ID: " . $id;

        // Insert into systemlog_tbl
        $log_stmt = $conn->prepare("INSERT INTO systemlog_tbl (user_id, user_type, systemlog_action, systemlog_date) VALUES (?, ?, ?, NOW())");
        $user_type = 'Admin';

        // Bind and execute the log insert
        $log_stmt->bind_param("sss", $user_id, $user_type, $action);
        $log_stmt->execute();
        $log_stmt->close();

        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Barangay deleted successfully.'
        ]);
    } else {
        // Return error response if deletion fails
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete barangay.'
        ]);
    }
} else {
    // Return error if 'id' is not set in the request
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request.'
    ]);
}
?>
