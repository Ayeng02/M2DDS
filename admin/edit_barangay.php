<?php
include '../includes/db_connect.php';

if (isset($_POST['barangay_id']) && isset($_POST['barangay_name']) && isset($_POST['barangay_fee']) && isset($_POST['barangay_route'])) {
    $barangayId = $_POST['barangay_id'];
    $barangayName = $_POST['barangay_name'];
    $barangayFee = $_POST['barangay_fee'];
    $barangayRoute = $_POST['barangay_route'];

    $query = "UPDATE Brgy_Tbl SET Brgy_Name = ?, Brgy_df = ?, brgy_route = ? WHERE Brgy_num = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sdsi", $barangayName, $barangayFee, $barangayRoute, $barangayId);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Barangay updated successfully.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update Barangay.'
        ]);
    }
}
?>
