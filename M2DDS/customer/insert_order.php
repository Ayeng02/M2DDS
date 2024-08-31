<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session and include the database connection
session_start();
include '../includes/db_connect.php'; // Database connection

// Set response header to JSON
header('Content-Type: application/json');

// Initialize the response array
$response = ['success' => false, 'error' => ''];

try {
    // Retrieve cart data from session
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    $productDetails = $cart['product_details'] ?? [];

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if customer ID is set
        if (!isset($_SESSION['cust_id'])) {
            throw new Exception("Customer ID not found.");
        }

        // Start transaction
        mysqli_begin_transaction($conn);

        $cust_id = $_SESSION['cust_id'];
        $fullname = $_POST['full-name'] ?? '';
        $phone = $_POST['phone-number'] ?? '';
        $barangay = $_POST['barangay'] ?? '';
        $purok = $_POST['purok'] ?? '';
        $province = $_POST['province'] ?? '';
        $mop = $_POST['mode-of-payment'] ?? '';
        $status_code = 1; 
        date_default_timezone_set('Asia/Manila');
        $order_date = date('Y-m-d H:i:s');

        $successfullyOrderedProducts = [];
        $productUpdates = []; 

        // Ensure $productDetails is an array and not empty
        if (is_array($productDetails) && count($productDetails) > 0) {
            foreach ($productDetails as $product) {
                $prod_code = $product['code'];
                $order_qty = $product['quantity'];
                $order_total = $product['price'] * $order_qty;
                $order_change = 0;

                // Lock the row for update
                $stmt = $conn->prepare("SELECT prod_qoh FROM product_tbl WHERE prod_code = ? FOR UPDATE");
                $stmt->bind_param('s', $prod_code);
                $stmt->execute();
                $stmt->bind_result($prod_qoh);
                $stmt->fetch();
                $stmt->close();

                if ($prod_qoh < $order_qty) {
                    throw new Exception("Insufficient stock for product code: $prod_code.");
                }

                // Insert order
                $stmt = $conn->prepare("CALL sp_InsertOrder(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param(
                    'ssssssssdddsd',
                    $cust_id,
                    $prod_code,
                    $fullname,
                    $phone,
                    $barangay,
                    $purok,
                    $province,
                    $mop,
                    $order_qty,
                    $order_total,
                    $order_change,
                    $order_date,
                    $status_code
                );
                

                if ($stmt->execute()) {
                    $successfullyOrderedProducts[] = $prod_code;
                    $productUpdates[] = [
                        'prod_code' => $prod_code,
                        'order_qty' => $order_qty
                    ];
                } else {
                    throw new Exception($stmt->error);
                }
            }
        } else {
            throw new Exception("Cart is empty or product details are not available.");
        }

        // Delete only the successfully ordered products from the cart
        if (!empty($successfullyOrderedProducts)) {
            $placeholders = implode(',', array_fill(0, count($successfullyOrderedProducts), '?'));
            $stmt = $conn->prepare("DELETE FROM cart_table WHERE cust_id = ? AND prod_code IN ($placeholders)");
            $types = str_repeat('s', count($successfullyOrderedProducts) + 1);
            $params = array_merge([$cust_id], $successfullyOrderedProducts);
            $stmt->bind_param($types, ...$params);

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
        }

        // Update the product quantity in stock
        foreach ($productUpdates as $update) {
            $stmt = $conn->prepare("UPDATE product_tbl SET prod_qoh = prod_qoh - ? WHERE prod_code = ?");
            $stmt->bind_param('ds', $update['order_qty'], $update['prod_code']);

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
        }

        // Commit transaction
        mysqli_commit($conn);

        // Clear the product details from the session
        unset($_SESSION['cart']['product_details']);

        // Set success response
        $response['success'] = true;
        $response['message'] = 'Order placed successfully.';
    }
} catch (Exception $e) {
    // Rollback transaction if an error occurs
    mysqli_rollback($conn);

    // Set error response
    $response['error'] = $e->getMessage();
}

// Return JSON response
echo json_encode($response);
?>
