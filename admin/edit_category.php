<?php
// Database connection
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_code = $_POST['category_code'];
    $category_name = $_POST['category_name'];
    $category_desc = $_POST['category_desc'];

    // Initialize an empty variable for the image path
    $image_path = null;

    // Check if a new image was uploaded
    if (isset($_FILES['category_img']) && $_FILES['category_img']['error'] == UPLOAD_ERR_OK) {
        // Handle the file upload
        $upload_dir = '../category/'; // Path to upload directory
        $image_file = $_FILES['category_img'];
        $image_name = basename($image_file['name']);
        $image_path = $upload_dir . $image_name;

        // Move the uploaded file to the target directory
        if (!move_uploaded_file($image_file['tmp_name'], $image_path)) {
            echo "Error uploading image.";
            exit;
        }
    } else {
        // If no new image was uploaded, fetch the current image path from the database
        $sql = "SELECT category_img FROM category_tbl WHERE category_code = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('s', $category_code);
            $stmt->execute();
            $stmt->bind_result($existing_image);
            $stmt->fetch();
            $stmt->close();

            // Use the existing image if no new image is uploaded
            $image_path = $existing_image;
        }
    }

    // Update the category in the database
    $sql = "UPDATE category_tbl SET 
                category_name = ?, 
                category_desc = ?, 
                category_img = ? 
            WHERE category_code = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ssss', $category_name, $category_desc, $image_path, $category_code);

        if ($stmt->execute()) {
            session_start();
            $_SESSION['success'] = 'Category updated successfully!';
            header("Location: addCategory.php?success=1");
        } else {
            echo "Error updating category: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing SQL: " . $conn->error;
    }

    $conn->close();
} else {
    echo "Invalid request method";
}
?>
