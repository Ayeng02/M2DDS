<?php
include '../includes/db_connect.php';
session_start(); // Start the session at the top

if (isset($_GET['id'])) {
    $category_code = $_GET['id']; // Get the category code from the URL

    // Check if there are products in this category before deleting
    $product_check_sql = "SELECT COUNT(*) as total_products FROM product_tbl WHERE category_code = ?";
    if ($product_stmt = $conn->prepare($product_check_sql)) {
        $product_stmt->bind_param('s', $category_code);
        $product_stmt->execute();
        $product_stmt->bind_result($total_products);
        $product_stmt->fetch();
        $product_stmt->close();

        if ($total_products > 0) {
            $_SESSION['delete_error'] = "Cannot delete category. There are $total_products product(s) associated with this category.";
            header("Location: addCategory.php");
            exit();
        }
    } else {
        echo "Error preparing product check statement: " . $conn->error;
        exit();
    }

    // Prepare the DELETE statement
    $sql = "DELETE FROM category_tbl WHERE category_code = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $category_code);

        if ($stmt->execute()) {
            $_SESSION['delete_success'] = 'The Category has been Deleted Successfully!';
        } else {
            $_SESSION['delete_error'] = "Error deleting category: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $_SESSION['delete_error'] = "Error preparing statement: " . $conn->error;
    }

    $conn->close();
    header("Location: addCategory.php");
    exit();
} else {
    echo "No category ID provided.";
}
?>
