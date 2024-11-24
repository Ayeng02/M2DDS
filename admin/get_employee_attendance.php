<?php
include '../includes/db_connect.php';

if (isset($_POST['emp_id'])) {
    $emp_id = $_POST['emp_id'];

    // Adjust query to retrieve logs for the current week for the specific employee
    $attData = "SELECT 
                    DATE_FORMAT(a.att_date, '%Y-%m-%d') AS `Date`, 
                    DATE_FORMAT(a.time_in, '%h:%i %p') AS `Time In`, 
                    DATE_FORMAT(a.time_out, '%h:%i %p') AS `Time Out`
                FROM 
                    att_tbl a 
                WHERE 
                    a.emp_id = ? 
                    AND YEARWEEK(a.att_date, 0) = YEARWEEK(CURRENT_DATE, 0) 
                ORDER BY 
                    a.att_date DESC";

    // Prepare statement and bind the parameter as a string
    $stmt = $conn->prepare($attData);
    $stmt->bind_param("s", $emp_id); // "s" because emp_id is varchar
    $stmt->execute();
    $result = $stmt->get_result();
    $attendance = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($attendance);
    $stmt->close();
    $conn->close();
}
?>
