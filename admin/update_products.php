<?php

include '../includes/db_connect.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prod_code = $_POST['prod_code'];
    $category_name = $_POST['category_name'];
    $prod_name = $_POST['prod_name'];
    $prod_price = $_POST['prod_price'];
    $prod_qoh = $_POST['prod_qoh'];
    $prod_discount = $_POST['prod_discount'];
    
    // Initialize an empty variable for the image path
    $image_path = null;

    // Check if a new image was uploaded
    if (isset($_FILES['prod_img']) && $_FILES['prod_img']['error'] == UPLOAD_ERR_OK) {
        // Handle the file upload
        $upload_dir = '../Product-Images/'; // Path to upload directory
        $relative_dir_path = 'Product-Images/';
        $image_file = $_FILES['prod_img'];
        $image_name = basename($image_file['name']);
        $image_path = $upload_dir . $image_name;
        $relative_path_to_store =   $relative_dir_path . $image_name;

        // Move the uploaded file to the target directory
        if (!move_uploaded_file($image_file['tmp_name'], $image_path)) {
            echo "Error uploading image.";
            exit;
        }
    } else {
        // If no new image was uploaded, fetch the current image path from the database
        $sql = "SELECT prod_img FROM product_tbl WHERE prod_code = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('s', $prod_code);
            $stmt->execute();
            $stmt->bind_result($existing_image);
            $stmt->fetch();
            $stmt->close();

            // Use the existing image if no new image is uploaded
            $relative_path_to_store = $existing_image;
        }
    }

    // Retrieve the category_code based on the category_name
    $sql = "SELECT category_code FROM category_tbl WHERE category_name = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $category_name);
        $stmt->execute();
        $stmt->bind_result($category_code);
        $stmt->fetch();
        $stmt->close();

        if (!$category_code) {
            echo "Invalid category name.";
            exit;
        }
    } else {
        echo "Error preparing SQL to fetch category_code: " . $conn->error;
        exit;
    }

    // Update the product_tbl with the retrieved category_code and new image path (or existing image path if not updated)
    $sql = "UPDATE product_tbl SET 
                category_code = ?, 
                prod_name = ?, 
                prod_price = ?, 
                prod_qoh = ?, 
                prod_discount = ?, 
                prod_img = ?
            WHERE prod_code = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ssdiiss', $category_code, $prod_name, $prod_price, $prod_qoh, $prod_discount, $relative_path_to_store, $prod_code);
        
        if ($stmt->execute()) {
            session_start();

             // Insert into system log upon successful employee update
             $user_id = $_SESSION['admin_id'];
             $action = "Update Product: " . $prod_code . "(" . $prod_name . ")";
             $user_type = 'Admin';
 
             // Prepare and execute system log insertion
             if ($log_stmt = $conn->prepare("INSERT INTO systemlog_tbl (user_id, user_type, systemlog_action, systemlog_date) VALUES (?, ?, ?, NOW())")) {
                 $log_stmt->bind_param("sss", $user_id, $user_type, $action);
                 $log_stmt->execute();
                 $log_stmt->close();
             } else {
                 $_SESSION['error'] = "Failed to log system action: " . $conn->error;
             }

            $_SESSION['success'] = 'The Product has been Updated Successfully!';
            header("Location: addproducts.php?success=1");
        } else {
            echo "Error updating product: " . $stmt->error;
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
