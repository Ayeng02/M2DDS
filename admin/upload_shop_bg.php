<?php
include '../includes/db_connect.php'; // Include your database connection

// Define the upload directory
$uploadDir = '../img/';

// Check if the file is uploaded
if (isset($_FILES['olshopmgt_bg'])) {
    $file = $_FILES['olshopmgt_bg'];
    
    // Get file information
    $fileName = basename($file['name']);
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = $file['type'];
    
    // Allowed file types
    $allowedTypes = ['image/jpeg', 'image/png'];
    
    // Validate file size (max 5MB)
    if ($fileSize > 5 * 1024 * 1024) {
        echo "<script>Swal.fire('Error', 'File size exceeds 5MB!', 'error');</script>";
        exit;
    }
    
    // Validate file type (only JPG, JPEG, PNG)
    if (!in_array($fileType, $allowedTypes)) {
        echo "<script>Swal.fire('Error', 'Only JPG, JPEG, or PNG files are allowed!', 'error');</script>";
        exit;
    }

    // Move the file to the upload directory with the original name
    if (move_uploaded_file($fileTmpName, $uploadDir . $fileName)) {
        // File path to be stored in the database
        $filePath = 'img/' . $fileName;

        // Update the database with the new image path (adjust based on your table structure)
        $query = "UPDATE olshopmgt_tbl SET olshopmgt_bg = '$filePath' WHERE olshopmgt_id = 1";  // Adjust the condition as necessary
        if ($conn->query($query)) {
            echo "<script>Swal.fire('Success', 'Background image uploaded successfully!', 'success');</script>";
            header('Location: shopmgt.php');
        } else {
            echo "<script>Swal.fire('Error', 'Failed to update the database!', 'error');</script>";
        }
    } else {
        echo "<script>Swal.fire('Error', 'Failed to upload the image!', 'error');</script>";
    }
}
?>
