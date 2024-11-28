<?php
    session_start();
use PhpOffice\PhpSpreadsheet\Chart\Title;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../includes/db_connect.php';

    $prod_code = $_POST['prod_code'];
    $category_name = $_POST['category_name'];
    $prod_name = $_POST['prod_name'];
    $prod_price = $_POST['prod_price'];
    $prod_discount = $_POST['prod_discount'];
    $qoh_action = $_POST['qoh_action']; // Either 'add' or 'subtract'
    $adjust_qoh = (float)$_POST['add_qoh']; // Quantity to adjust
    $image_path = null;

    // Check for duplicate product name
$sql = "SELECT prod_code FROM product_tbl WHERE prod_name = ? AND prod_code != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $prod_name, $prod_code);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {

    // Set alert data in session
    $_SESSION['alert'] = [
        'icon' => 'error',
        'title' => "Product name already exists. Please choose a different name."
    ];
    // Redirect to the add products page
    header("Location: addproducts.php?alert=1");
    exit; // Ensure no further code is executed
}

$stmt->close();

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

    // Get category code
    $sql = "SELECT category_code FROM category_tbl WHERE category_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $category_name);
    $stmt->execute();
    $stmt->bind_result($category_code);
    $stmt->fetch();
    $stmt->close();

    if (!$category_code) {
        echo "";
        $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => "Invalid category."
        ];
        // Redirect to the add products page
        header("Location: addproducts.php?alert=1");
        exit; // Ensure no further code is executed
        exit;
    }

    // Get current QOH
    $sql = "SELECT prod_qoh FROM product_tbl WHERE prod_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $prod_code);
    $stmt->execute();
    $stmt->bind_result($current_qoh);
    $stmt->fetch();
    $stmt->close();

    $current_qoh = (float)$current_qoh;

    // Calculate new QOH based on action
    if ($qoh_action === 'add') {
        $new_qoh = $current_qoh + $adjust_qoh;
    } elseif ($qoh_action === 'subtract') {
        $new_qoh = $current_qoh - $adjust_qoh;

        if ($new_qoh < 0) {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => "Quantity on hand cannot be negative."
            ];
            // Redirect to the add products page
            header("Location: addproducts.php?alert=1");
            exit; // Ensure no further code is executed
        
        }
    } else {
        $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => "Invalid quantity action."
        ];
        // Redirect to the add products page
        header("Location: addproducts.php?alert=1");
        exit; // Ensure no further code is executed
        
    }

    // Update product
    $sql = "UPDATE product_tbl SET 
                category_code = ?, 
                prod_name = ?, 
                prod_price = ?, 
                prod_qoh = ?, 
                prod_discount = ?, 
                prod_img = ? 
            WHERE prod_code = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssdiiss', $category_code, $prod_name, $prod_price, $new_qoh, $prod_discount,   $relative_path_to_store, $prod_code);

    if ($stmt->execute()) {
        session_start();

        $user_id = $_SESSION['admin_id'];
        $action = "Updated Product: $prod_code ($prod_name)";
        $stmtLog = $conn->prepare("INSERT INTO systemlog_tbl (user_id, user_type, systemlog_action, systemlog_date) VALUES (?, 'Admin', ?, NOW())");
        $stmtLog->bind_param('is', $user_id, $action);
        $stmtLog->execute();

        $_SESSION['success'] = 'Product updated successfully!';
        header("Location: addproducts.php?success=1");
    } else {
        echo "Error updating product: " . $stmt->error;
        
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}

?>
