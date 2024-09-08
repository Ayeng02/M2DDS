<?php
// check_new_orders.php

// Database connection
include '../includes/db_connect.php';

header('Content-Type: application/json');

// Query to get the latest order ID
$query = "SELECT MAX(order_id) AS latest_order_id FROM order_tbl";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo json_encode([
        'latest_order_id' => $row['latest_order_id']
    ]);
} else {
    echo json_encode([
        'error' => 'Database query failed'
    ]);
}

mysqli_close($conn);
?>
