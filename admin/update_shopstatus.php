<?php
include '../includes/db_connect.php';
// Update shop status
if (isset($_POST['status'])) {
    $status = $_POST['status'] === 'Open' ? 'Open' : 'Close';
    $update_query = "UPDATE shopstatus_tbl SET shopstatus = '$status'";

    if ($conn->query($update_query) === TRUE) {
        echo "Shop status updated to " . $status;
    } else {
        echo "Error updating shop status: " . $conn->error;
    }
}

$conn->close();
?>
