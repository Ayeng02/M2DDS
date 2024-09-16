<?php
include '../includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$orderIds = isset($_POST['orderIds']) ? $_POST['orderIds'] : [];
if (empty($orderIds) || !is_array($orderIds)) {
    echo json_encode(['error' => 'No order IDs provided']);
    exit;
}

// Sanitize the order IDs to prevent SQL injection
$orderIds = array_map(function($id) use ($conn) {
    return mysqli_real_escape_string($conn, $id);
}, $orderIds);

// SQL query to get order details along with Brgy_df
$sql = "SELECT o.order_id, o.order_date, o.order_fullname, o.order_phonenum, o.cust_id,
               SUM(o.order_total) as order_total, 
               SUM(o.order_cash) as order_cash, 
               SUM(o.order_change) as order_change, 
               o.order_barangay,
               b.Brgy_df,
               p.prod_name, 
               SUM(o.order_qty) as total_qty, 
               p.prod_price
        FROM Order_tbl o 
        JOIN product_tbl p ON o.prod_code = p.prod_code
        JOIN brgy_tbl b ON o.order_barangay = b.Brgy_Name
        WHERE o.order_id IN ('" . implode("','", $orderIds) . "') 
        GROUP BY o.cust_id, o.order_date, o.order_fullname, o.order_phonenum, o.order_barangay, b.Brgy_df, p.prod_name, p.prod_price";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
    exit;
}

$orders = [];

while ($row = $result->fetch_assoc()) {
    $orderId = $row['order_id'];
    
    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'order_id' => $row['order_id'],
            'order_date' => $row['order_date'],
            'order_fullname' => $row['order_fullname'],
            'order_phonenum' => $row['order_phonenum'],
            'order_total' => $row['order_total'], 
            'order_cash' => $row['order_cash'],
            'order_change' => $row['order_change'],
            'Brgy_df' => $row['Brgy_df'],
            'items' => []

        ];
    }
    
    $orders[$orderId]['items'][] = [
        'prod_name' => $row['prod_name'],
        'qty' => $row['total_qty'],
        'unit_price' => $row['prod_price'],
        'delivery_fee' => $row['Brgy_df'],
        'total' => $row['order_total']
       
    ];
}

// Convert associative array to indexed array
echo json_encode(array_values($orders));
?>

