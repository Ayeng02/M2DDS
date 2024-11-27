<?php
include '../includes/db_connect.php'; 

// Start the session
session_start();

// Retrieve emp_id from the session
$emp_id = $_SESSION['emp_id'] ?? null; // Use null if emp_id is not set

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
        $types = str_repeat('s', count($order_ids));

        // Bind the order IDs dynamically
        $stmt->bind_param($types, ...$order_ids);

        // Execute the statement and check for success
        if ($stmt->execute()) {
            // Log the action in the emplog_tbl
            $log_query = "INSERT INTO emplog_tbl (emp_id, order_id, emplog_status, emplog_action, emplog_date) VALUES (?, ?, ?, ?, NOW())";
            if ($log_stmt = $conn->prepare($log_query)) {
                
                $emplog_status = 2; //Processing
                foreach ($order_ids as $order_id) {
                    $log_stmt->bind_param('ssis', $emp_id, $order_id, $emplog_status, $action);
                    $action = 'Accepted Order'; 
                    $log_stmt->execute();
                }
                $log_stmt->close();
            }

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
