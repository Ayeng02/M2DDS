<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'add_product_') === 0) {
            $emp_id = str_replace('add_product_', '', $key);
            $add_product = $value;
            $edit_product = $_POST['edit_product_' . $emp_id];
            $add_category = $_POST['add_category_' . $emp_id];

            // Insert or Update the access control record for this employee with granted_date set to NOW()
            $query = "INSERT INTO access_control (emp_id, add_product, edit_product, add_category, granted_date) 
                      VALUES ('$emp_id', '$add_product', '$edit_product', '$add_category', NOW()) 
                      ON DUPLICATE KEY UPDATE add_product='$add_product', edit_product='$edit_product', add_category='$add_category', granted_date=NOW()";

            mysqli_query($conn, $query);
        }
    }

    
    header('Location: rbac.php');
}
?>
