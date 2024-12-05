<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_SESSION['admin_id']; // Assuming admin_id is stored in session
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];

    // Update admin info in the database
    $stmt = $conn->prepare("UPDATE admin_tbl SET admin_name = ?, admin_email = ?, admin_num = ? WHERE admin_id = ?");
    $stmt->bind_param("sssi", $full_name, $email, $contact, $admin_id);

    if ($stmt->execute()) {
       
          $_SESSION['alert'] = [
                    'icon' => 'success',
                     'title' =>  'Admin info updated successfully.'
                                                    ];
        header("Location: admin_profile.php");
        exit();
    } else {
        
            $_SESSION['alert'] = [
                    'icon' => 'Error',
                     'title' =>  'Failed to update admin info.'
                                                    ];
        header("Location: admin_profile.php");
        exit();
    }
}
?>
