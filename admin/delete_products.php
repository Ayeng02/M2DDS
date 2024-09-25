<?php
include '../includes/db_connect.php';

if (isset($_GET['id'])) {
    $prod_code = $_GET['id']; // Get the product code from the URL

    // Prepare the DELETE statement
    $sql = "DELETE FROM product_tbl WHERE prod_code = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        // Bind the product code to the statement
        $stmt->bind_param('s', $prod_code);

        // Execute the statement
        if ($stmt->execute()) {
           session_start();
           $_SESSION['delete_success'] = 'The Product has been Deleted Successfully!';
            header("Location: addproducts.php?delete_success=1");
            exit();
        } else {
            // Handle any error
            echo "Error deleting product: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    // Close the connection
    $conn->close();
} else {
    echo "No product ID provided.";
}
