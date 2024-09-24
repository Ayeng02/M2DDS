<?php
    session_start();

    // Include your database connection file
    include('../includes/db_connect.php');

    // Get the POST data
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['cust_id']) && isset($data['sender'])) {
        $cust_id = $data['cust_id'];
        $sender = $data['sender'];

        // Prepare the SQL query to update unread messages for the order_manager
        $query = "UPDATE chat_messages 
                  SET is_read = 1 
                  WHERE cust_id = ? AND sender = ? AND is_read = 0";

        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('ss', $cust_id, $sender);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Messages marked as read.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No unread messages found.']);
            }

            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement.']);
        }

        $conn->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
    }
?>
