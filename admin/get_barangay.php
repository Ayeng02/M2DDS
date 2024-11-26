<?php
include '../includes/db_connect.php';

if (isset($_GET['id'])) {
    $barangayId = $_GET['id'];

    $query = "SELECT * FROM brgy_tbl WHERE Brgy_num = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $barangayId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $barangay = $result->fetch_assoc();
        echo json_encode([
            'status' => 'success',
            'barangay' => $barangay
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Barangay not found.'
        ]);
    }
}
?>
