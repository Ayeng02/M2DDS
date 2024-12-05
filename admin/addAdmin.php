<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $admin_name = $_POST['admin_name'];
    $admin_email = $_POST['admin_email'];
    $admin_contact = $_POST['admin_num'];
    $admin_password = $_POST['admin_pass'];

    // Hash the password
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

    // Assuming you have a database connection set up
    // $conn is your database connection

    $query = "INSERT INTO admin_tbl (admin_name, admin_email, admin_num, admin_pass) 
              VALUES ('$admin_name', '$admin_email', '$admin_contact', '$hashed_password')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] =  "Admin added successfully.";
        header("Location: admin_profile.php?success=1");
    } else {
        $_SESSION['alert'] = "Error: " . mysqli_error($conn);
         $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => "Invalid category."
        ];
        // Redirect to the add products page
        header("Location: admin_profile.php?alert=1");
        exit; // Ensure no further code is executed
    }
}
?>
