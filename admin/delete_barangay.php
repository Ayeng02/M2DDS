<?php
include '../includes/db_connect.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    $deleteQuery = "DELETE FROM Brgy_Tbl WHERE Brgy_num = '$id'";
    if (mysqli_query($conn, $deleteQuery)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Barangay deleted successfully.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete barangay.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request.'
    ]);
}
?>
