<?php
include '../includes/db_connect.php'; // Include your database connection

// Define the upload directory
$uploadDir = '../img/';

// Check if the file is uploaded
if (isset($_FILES['olshopmgt_vid'])) {
    $file = $_FILES['olshopmgt_vid'];
    
    // Get file information
    $fileName = basename($file['name']);
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = $file['type'];
    
    // Allowed file type
    $allowedType = 'video/mp4';
    
    // Validate file size (max 50MB)
    if ($fileSize > 50 * 1024 * 1024) {
        echo "<script>Swal.fire('Error', 'File size exceeds 50MB!', 'error');</script>";
        exit;
    }
    
    // Validate file type (only MP4)
    if ($fileType !== $allowedType) {
        echo "<script>Swal.fire('Error', 'Only MP4 files are allowed!', 'error');</script>";
        exit;
    }

    // Move the file to the upload directory with the original name
    if (move_uploaded_file($fileTmpName, $uploadDir . $fileName)) {
        // File path to be stored in the database
        $filePath = 'img/' . $fileName;

        // Update the database with the new video path (adjust based on your table structure)
        $query = "UPDATE olshopmgt_tbl SET olshopmgt_vid = '$filePath' WHERE olshopmgt_id = 1";  // Adjust the condition as necessary
        if ($conn->query($query)) {
            echo "<script>Swal.fire('Success', 'Video uploaded successfully!', 'success');</script>";
            header('Location: shopmgt.php');
        } else {
            echo "<script>Swal.fire('Error', 'Failed to update the database!', 'error');</script>";
        }
    } else {
        echo "<script>Swal.fire('Error', 'Failed to upload the video!', 'error');</script>";
    }
}
?>
