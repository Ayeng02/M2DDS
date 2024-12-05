<?php
session_start(); // Start the session

// Include database connection
require_once '../includes/db_connect.php'; // Replace with your database connection file path


if (isset($_SESSION['emp_id'])) {
    $emp_id = $_SESSION['emp_id'];

    // Prepare the SQL query to update employee status
    $statusUpdateSql = "UPDATE emp_tbl SET emp_status = 'Inactive' WHERE emp_id = ?";
    $stmt = $conn->prepare($statusUpdateSql);

    if ($stmt) {
        $stmt->bind_param("s", $emp_id); // Bind the emp_id to the query
        $stmt->execute();
        $stmt->close();
    }
}

// Destroy all session data
session_unset(); // Remove all session variables
session_destroy(); // Destroy the session

// Redirect to the login page
header('Location: ../login.php');
exit();
?>
