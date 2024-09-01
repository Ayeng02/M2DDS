<?php
session_start(); // Ensure session is started

include '../includes/db_connect.php';

// Handle item removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_prod_code']) && isset($_SESSION['cust_id'])) {
    $prod_code = $_POST['remove_prod_code'];
    $cust_id = $_SESSION['cust_id'];

    $sql = "DELETE FROM cart_table WHERE prod_code = ? AND cust_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $prod_code, $cust_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    $stmt->close();
    $conn->close();
    exit();
}
?>
