<?php
// get_unseen_notification_count.php
include('db_connect.php'); 

// Query to get the count of unseen notifications
$sql = "SELECT COUNT(*) AS unseen_count FROM adminnotif_tbl WHERE notif_status = 'unseen'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo $row['unseen_count'];
} else {
    echo 0;  // No unseen notifications
}
?>
