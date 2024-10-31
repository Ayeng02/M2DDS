<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prod_name = $_POST['prod_name'];

    // Prepare and execute query to check for duplicates
    $stmt = $conn->prepare("SELECT COUNT(*) FROM product_tbl WHERE prod_name = ?");
    $stmt->bind_param("s", $prod_name);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();

    // Return response
    echo $count > 0 ? 'duplicate' : 'ok';

    $stmt->close();
}
?>
