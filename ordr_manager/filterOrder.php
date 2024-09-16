<?php
include '../includes/db_connect.php';

$filter = isset($_POST['filter']) ? $_POST['filter'] : 'default';

switch ($filter) {
    case 'az':
        $sql = "
        SELECT o.order_id, p.prod_name, o.order_fullname, o.order_phonenum, 
        CONCAT(o.order_purok, ', ', o.order_barangay, ', ', o.order_province) AS order_address, 
        o.order_qty, o.order_total, o.order_date, b.Brgy_name, b.Brgy_df, p.prod_price, p.prod_discount
        FROM order_tbl o
        JOIN product_tbl p ON o.prod_code = p.prod_code 
        JOIN brgy_tbl b ON o.order_barangay = b.Brgy_Name 
        WHERE status_code = 2 AND DATE(o.order_date) = CURDATE()
        ORDER BY o.order_barangay ASC";
        break;
    case 'za':
        $sql = "
        SELECT o.order_id, p.prod_name, o.order_fullname, o.order_phonenum, 
        CONCAT(o.order_purok, ', ', o.order_barangay, ', ', o.order_province) AS order_address, 
        o.order_qty, o.order_total, o.order_date, b.Brgy_name, b.Brgy_df, p.prod_price, p.prod_discount
        FROM order_tbl o
        JOIN product_tbl p ON o.prod_code = p.prod_code
        JOIN brgy_tbl b ON o.order_barangay = b.Brgy_Name 
        WHERE status_code = 2 AND DATE(o.order_date) = CURDATE()
        ORDER BY o.order_barangay DESC";
        break;
    default:
        // brgy_route filtering case
        $sql = "
        SELECT o.order_id, p.prod_name, o.order_fullname, o.order_phonenum, 
        CONCAT(o.order_purok, ', ', o.order_barangay, ', ', o.order_province) AS order_address, 
        o.order_qty, o.order_total, o.order_date, b.Brgy_route, b.Brgy_df, p.prod_price, p.prod_discount 
        FROM order_tbl o
        JOIN product_tbl p ON o.prod_code = p.prod_code 
        JOIN brgy_tbl b ON o.order_barangay = b.Brgy_Name 
        WHERE b.brgy_route = '$filter' AND status_code = 2 AND DATE(o.order_date) = CURDATE()
        ORDER BY o.order_date ASC";
        break;
}

$processingResult = $conn->query($sql);

if ($processingResult->num_rows > 0) {
    while($row = $processingResult->fetch_assoc()) {
        echo "<tr>
                <td><input type='checkbox' class='processingCheckbox' value='" . htmlspecialchars($row['order_id']) . "' data-bs-toggle='tooltip' data-bs-placement='right' title='Check to select Order'></td>
                <td>" . htmlspecialchars($row['order_id']) . "</td>
                <td>" . htmlspecialchars($row['prod_name']) . "</td>
                <td>" . htmlspecialchars($row['order_fullname']) . "</td>
                <td>" . htmlspecialchars($row['order_phonenum']) . "</td>
                <td>" . htmlspecialchars($row['order_address']) . "</td>";

        // Conditional rendering for prod_discount and prod_price
        if ($row['prod_discount'] > 0) {
            echo "<td>" . htmlspecialchars($row['prod_discount']) . "</td>";
        } else {
            echo "<td>" . htmlspecialchars($row['prod_price']) . "</td>";
        }

        echo "<td>" . htmlspecialchars($row['Brgy_df']) . "</td>
              <td>" . htmlspecialchars($row['order_qty']) . "</td>
              <td>" . htmlspecialchars(number_format($row['order_total'], 2)) . "</td>
              <td>" . htmlspecialchars(date('F j, Y g:i A', strtotime($row['order_date']))) . "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='11' class='text-center'>No processing orders found</td></tr>";
}

$conn->close();
?>
