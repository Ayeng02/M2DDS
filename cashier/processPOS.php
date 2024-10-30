<?php
// Database connection
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the POST data
    $productData = json_decode($_POST['productData'], true); // Decode the JSON data into an array
    $amount_received = $_POST['amount_received'];
    $pos_change = $_POST['pos_change'];
    $pos_personnel = $_POST['pos_personnel'];
    $transac_date = $_POST['transac_date'];

    $pos_change = 0;

    // Begin transaction
    $conn->begin_transaction();

    try {
        foreach ($productData as $product) {
            $prod_code = $product['prod_code'];
            $pos_qty = $product['pos_qty'];
            $pos_discount = $product['pos_discount'];
            $total_amount = $product['total_amount'];

            $pos_change = $amount_received - $total_amount;


            // Call the stored procedure for each product
            $stmt = $conn->prepare("CALL sp_insertPOStransaction(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                'sddddsss', 
                $prod_code, 
                $pos_qty, 
                $pos_discount, 
                $total_amount, 
                $amount_received, 
                $pos_change, 
                $pos_personnel, 
                $transac_date
            );

            if (!$stmt->execute()) {
                throw new Exception('Failed to execute stored procedure for product: ' . $prod_code);
            }

            $stmt->close();
        }

        // Commit the transaction if everything is successful
        $conn->commit();
        echo 'success';
    } catch (Exception $e) {
        // Rollback if there is any error
        $conn->rollback();
        echo 'error';
    }

    $conn->close();
}
