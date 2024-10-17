<?php
session_start(); // Start the session
include 'db_connect.php'; // Include your database connection file


// Check if user is logged in and session has emp_id
if (isset($_SESSION['emp_id'])) {
    $emp_id = $_SESSION['emp_id'];

    // Call the stored function to get the employee info (full name and image)
    $query = "SELECT sf_getEmpInfo(?) AS emp_info";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $emp_info = $row['emp_info'];

        // Split the result to get the full name and image
        list($emp_fullname, $emp_img) = explode('|', $emp_info);
    } else {
        // Handle case where emp_id does not exist
        echo "User not found.";
        exit();
    }
} else {
    // Redirect to login page if emp_id is not found in session
    header("Location: ../login.php");
    exit();
}
?>