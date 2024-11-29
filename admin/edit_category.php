<?php
session_start();
// Database connection
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_code = $_POST['category_code'];
    $category_name = $_POST['category_name'];
    $category_desc = $_POST['category_desc'];

    // Initialize an empty variable for the image path
    $image_path = null;

    $sql = "SELECT category_code FROM category_tbl WHERE category_name = ? AND category_code != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $category_name, $category_code);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
    
        // Set alert data in session
        $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => "Category name already exists. Please choose a different name."
        ];
        // Redirect to the add products page
        header("Location: addCategory.php?alert=1");
        exit; // Ensure no further code is executed
    }

    // Check if a new image was uploaded
    if (isset($_FILES['category_img']) && $_FILES['category_img']['error'] == UPLOAD_ERR_OK) {
        // Handle the file upload
        $upload_dir = '../category/'; // Path to upload directory
         $relative_dir_path = 'category/';
        $image_file = $_FILES['category_img'];
        $image_name = basename($image_file['name']);
        $image_path = $upload_dir . $image_name;
         $relative_path_to_store =   $relative_dir_path . $image_name;

        // Move the uploaded file to the target directory
        if (!move_uploaded_file($image_file['tmp_name'], $image_path)) {
            
             $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => "Error uploading image."
        ];
        // Redirect to the add products page
        header("Location: addCategory.php?alert=1");
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
            $relative_path_to_store = $existing_image;
        }
    }

    // Update the category in the database
    $sql = "UPDATE category_tbl SET 
                category_name = ?, 
                category_desc = ?, 
                category_img = ? 
            WHERE category_code = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ssss', $category_name, $category_desc, $relative_path_to_store, $category_code);

        if ($stmt->execute()) {
            session_start();

            // Insert into system log
            $user_id = $_SESSION['admin_id'];
            $action = "Edit category: " . $category_name;

            $log_stmt = $conn->prepare("INSERT INTO systemlog_tbl (user_id, user_type, systemlog_action, systemlog_date) VALUES (?, ?, ?, NOW())");
            $user_type = 'Admin';
            $log_stmt->bind_param("sss", $user_id, $user_type, $action);
            $log_stmt->execute();
            $log_stmt->close();

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
