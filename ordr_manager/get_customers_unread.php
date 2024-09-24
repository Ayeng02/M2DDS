<?php
include '../includes/db_connect.php';

// Query to fetch customer details and unread messages count
$sql = "SELECT cm.cust_id, 
               CONCAT(c.f_name, ' ', c.l_name) AS customer, 
               SUM(CASE WHEN cm.is_read = 0 AND cm.sender = 'customer' THEN 1 ELSE 0 END) AS unread_count,
               MAX(cm.timestamp) AS last_message_time  -- Get the latest message timestamp for each cust_id
        FROM chat_messages cm
        JOIN customers c ON cm.cust_id = c.cust_id
        GROUP BY cm.cust_id
        ORDER BY last_message_time DESC";  // Order by the newest message timestamp

$result = $conn->query($sql);

// Prepare an array to hold the results
$customers = [];

while ($row = $result->fetch_assoc()) {
    $customers[] = [
        'cust_id' => $row['cust_id'],
        'customer' => $row['customer'],
        'unread_count' => $row['unread_count']
    ];
}

// Return the data as a JSON response
echo json_encode($customers);

$conn->close();
?>
