<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barangay_name = mysqli_real_escape_string($conn, $_POST['barangay_name']);
    $barangay_fee = mysqli_real_escape_string($conn, $_POST['barangay_fee']);
    $barangay_route = mysqli_real_escape_string($conn, $_POST['barangay_route']);

    // Check if Barangay Name already exists
    $checkQuery = "SELECT * FROM brgy_tbl WHERE Brgy_Name = '$barangay_name'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Barangay already exists.'
        ]);
        exit();
    }

    // Insert Barangay
    $insertQuery = "INSERT INTO brgy_tbl (Brgy_Name, Brgy_df, brgy_route) 
                    VALUES ('$barangay_name', '$barangay_fee', '$barangay_route')";
    if (mysqli_query($conn, $insertQuery)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Barangay added successfully.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add barangay.'
        ]);
    }
}
?>
