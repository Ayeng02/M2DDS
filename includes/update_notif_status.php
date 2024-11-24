<?php
// update_notif_status.php
include('db_connect.php'); 

if (isset($_POST['notif_id'])) {
    $notifId = $_POST['notif_id'];

    // Update the status to 'seen'
    $sql = "UPDATE adminnotif_tbl SET notif_status = 'seen' WHERE notif_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $notifId);

    if ($stmt->execute()) {
        echo "Notification status updated.";
    } else {
        echo "Error updating notification status.";
    }

    $stmt->close();
}
?>
