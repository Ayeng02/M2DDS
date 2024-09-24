<?php
include '../includes/db_connect.php';

if (isset($_GET['cust_id'])) {
    $cust_id = $conn->real_escape_string($_GET['cust_id']); // Sanitize input

    $sql = "SELECT sender, message, timestamp FROM chat_messages WHERE cust_id = '$cust_id' ORDER BY timestamp ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sender = htmlspecialchars($row['sender']);
            $message = htmlspecialchars($row['message']);
            $timestamp = date('F j, Y h:i A', strtotime($row['timestamp'])); // Format timestamp

            if ($sender == 'customer') {
                echo "<div class='message-user'>
                        $message
                        <div class='message-timestamp'>[$timestamp]</div>
                      </div>";
            } else {
                echo "<div class='message-admin'>
                        $message
                        <div class='message-timestamp'>[$timestamp]</div>
                      </div>";
            }
        }
    } else {
        echo "<div>No messages found.</div>";
    }
} else {
    echo "<div>Invalid customer ID.</div>";
}

$conn->close();
?>
