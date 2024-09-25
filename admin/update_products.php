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
        $image_file = $_FILES['prod_img'];
        $image_name = basename($image_file['name']);
        $image_path = $upload_dir . $image_name;

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
            $image_path = $existing_image;
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
        $stmt->bind_param('ssdiiss', $category_code, $prod_name, $prod_price, $prod_qoh, $prod_discount, $image_path, $prod_code);
        
        if ($stmt->execute()) {
            session_start();
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
