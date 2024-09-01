<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['cart'] = [
        'selected_items' => $_POST['selected_items'],
        'total' => $_POST['total'],
        'product_details' => json_decode($_POST['product_details'], true)
    ];
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
?>
