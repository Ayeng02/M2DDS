<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = $_POST['category_name'];

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT COUNT(*) FROM category_tbl WHERE category_name = ?");
    $stmt->bind_param("s", $categoryName);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    // Check if category name exists
    if ($count > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
}
?>
