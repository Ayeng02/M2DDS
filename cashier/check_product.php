<?php
session_start();
include '../includes/db_connect.php';

$response = ['success' => false];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchCode = $_POST['productCode'];

    // Prepare SQL statement to search for the product
    $stmt = $conn->prepare("SELECT prod_name, prod_price, prod_qoh, prod_discount FROM product_tbl WHERE prod_code = ? OR prod_name = ?");
    $stmt->bind_param("ss", $searchCode, $searchCode);
    $stmt->execute();
    $stmt->store_result();

    // Check if a product is found
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($productName, $productPrice, $itemStocks, $prodDiscount);
        $stmt->fetch();

        
   // Determine the product price based on discount
   if ($prodDiscount > 0) {
    $productPrice = $prodDiscount; // Use discount price if it's greater than 0
  }

        // Prepare the response
        $response['success'] = true;
        $response['productName'] = $productName;
        $response['productPrice'] = $productPrice;
        $response['itemStocks'] = $itemStocks;
    }

    $stmt->close();
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($response);
?>
