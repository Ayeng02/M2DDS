<?php 
include '../includes/db_connect.php'; 
// fetchOrders.php
if (isset($_POST['order_date'])) {
    $orderDate = $_POST['order_date'];

   $query = "SELECT 
        prod_name, 
        SUM(total_ordered_price) AS total_ordered_price
    FROM (
        SELECT 
            p.prod_name, 
            SUM(o.order_qty * o.order_total) AS total_ordered_price
        FROM 
            order_tbl o
        JOIN 
            product_tbl p ON o.prod_code = p.prod_code
        WHERE 
            o.status_code = '4'
            AND DATE(o.order_date) = DATE('$orderDate')  -- Filter by order_date

        GROUP BY 
            p.prod_name

        UNION ALL

        SELECT 
            p.prod_name, 
            SUM(o.pos_qty * p.prod_price) AS total_ordered_price
        FROM 
            pos_tbl o
        JOIN 
            product_tbl p ON o.prod_code = p.prod_code
        WHERE 
            DATE(o.transac_date) = DATE('$orderDate')  -- Filter by transaction_date
        GROUP BY 
            p.prod_name
    ) AS combined_sales
    GROUP BY 
        prod_name
    ORDER BY 
        total_ordered_price DESC";

    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['prod_name']) . "</td>
                    <td>₱ " . number_format($row['total_ordered_price'], 2) . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='2'>No data available</td></tr>";
    }
}
?>
