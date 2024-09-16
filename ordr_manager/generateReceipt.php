<?php
include '../includes/db_connect.php';  // Assuming db_connect.php has your database connection

if (isset($_POST['orders']) && !empty($_POST['orders'])) {
    $orderIds = $_POST['orders'];

    // Fetch all orders based on selected order IDs
    $orderQuery = "SELECT * FROM order_tbl WHERE order_id IN ('" . implode("','", $orderIds) . "')";
    $result = $conn->query($orderQuery);

    if ($result->num_rows > 0) {
        $receipts = [];

        // Group orders by cust_id
        while ($row = $result->fetch_assoc()) {
            $cust_id = $row['cust_id'];
            if (!isset($receipts[$cust_id])) {
                $receipts[$cust_id] = [];
            }
            $receipts[$cust_id][] = $row;
        }

 
        // Pass data as JSON or generate directly to print
        echo json_encode($receipts);
    } else {
        echo json_encode(['error' => 'No orders found']);
    }
}
?>
