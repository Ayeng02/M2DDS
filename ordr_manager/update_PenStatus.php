<?php
include '../includes/db_connect.php'; // Include your database connection

// Read the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if order_ids is set
if (isset($data['order_ids']) && is_array($data['order_ids'])) {
    $order_ids = $data['order_ids'];

    // Create placeholders for the order IDs
    $placeholders = implode(',', array_fill(0, count($order_ids), '?'));

    // Prepare the SQL query
    $query = "UPDATE order_tbl SET status_code = 2 WHERE order_id IN ($placeholders)";
    
    // Prepare the statement
    if ($stmt = $conn->prepare($query)) {
        // Bind the order IDs dynamically
        $types = str_repeat('s', count($order_ids)); // 'i' for integers

        // Bind the order IDs dynamically
        $stmt->bind_param($types, ...$order_ids);

        // Execute the statement and check for success
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }

        $stmt->close();
    } else {
        // Error preparing the statement
        echo json_encode(['success' => false, 'error' => 'Failed to prepare the SQL statement']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}

$conn->close();
?>
