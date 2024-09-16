<?php

include '../includes/db_connect.php';

$query = "SELECT emp_id, CONCAT(emp_fname, ' ', emp_lname) AS emp_name 
          FROM emp_tbl 
          WHERE emp_role = 'Shipper' AND emp_status = 'Active'";

$result = mysqli_query($conn, $query);

$employees = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
}

echo json_encode($employees);
?>
