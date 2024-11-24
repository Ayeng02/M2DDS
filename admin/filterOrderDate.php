<?php
// Include database connection
include '../includes/db_connect.php'; // Adjust path as necessary

// Get the selected date and pagination parameters
$selectedDate = $_GET['order_date'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

// Fetch total number of orders for pagination
$count_sql = "SELECT COUNT(*) AS total 
              FROM order_tbl o
              JOIN status_tbl s ON o.status_code = s.status_code
              WHERE o.order_date LIKE '%$selectedDate%'";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch the filtered orders
$sql = "SELECT o.order_id, o.order_fullname, o.order_phonenum, 
        CONCAT(o.order_purok, ' ', o.order_barangay, ', ', o.order_province) AS order_address,
        o.order_mop, o.order_qty, o.order_total, o.order_cash, o.order_change, s.status_name, o.order_date 
        FROM order_tbl o
        JOIN status_tbl s ON o.status_code = s.status_code
        WHERE o.order_date LIKE '%$selectedDate%'
        LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

// Output the table rows for the orders
if ($result->num_rows > 0) {
    echo '<tbody id="order-rows">'; // Start table body marker
    while ($order = $result->fetch_assoc()) {
        echo '<tr>
                <td>' . htmlspecialchars($order['order_id']) . '</td>
                <td>' . htmlspecialchars($order['order_fullname']) . '</td>
                <td>' . htmlspecialchars($order['order_phonenum']) . '</td>
                <td>' . htmlspecialchars($order['order_address']) . '</td>
                <td>' . htmlspecialchars($order['order_mop']) . '</td>
                <td>' . htmlspecialchars(number_format($order['order_qty'])) . '</td>
                <td>' . htmlspecialchars(number_format($order['order_total'])) . '</td>
                <td>' . htmlspecialchars(number_format($order['order_cash'])) . '</td>
                <td>' . htmlspecialchars(number_format($order['order_change'])) . '</td>
                <td>' . htmlspecialchars($order['status_name']) . '</td>
                <td>
                    <a href="#" class="edit-icon" data-bs-toggle="modal" data-bs-target="#editModal"
                        data-order-id="' . htmlspecialchars($order['order_id']) . '"
                        data-full-name="' . htmlspecialchars($order['order_fullname']) . '"
                        data-contact="' . htmlspecialchars($order['order_phonenum']) . '"
                        data-address="' . htmlspecialchars($order['order_address']) . '"
                        data-mop="' . htmlspecialchars($order['order_mop']) . '"
                        data-qty="' . htmlspecialchars(number_format($order['order_qty'])) . '"
                        data-total="' . htmlspecialchars(number_format($order['order_total'])) . '"
                        data-cash="' . htmlspecialchars(number_format($order['order_cash'])) . '"
                        data-change="' . htmlspecialchars(number_format($order['order_change'])) . '"
                        data-status="' . htmlspecialchars($order['status_name']) . '">
                        <i class="fa fa-edit"></i>
                    </a>
                    <a href="order_delete.php?id=' . htmlspecialchars($order['order_id']) . '" class="delete-icon" onclick="confirmDelete(event, \'' . htmlspecialchars($order['order_id']) . '\')">
                        <i class="fa fa-trash"></i>
                    </a>
                </td>
              </tr>';
    }
    echo '</tbody>'; // End table body marker
} else {
    echo '<tbody id="order-rows"><tr><td colspan="11">No orders found for this date.</td></tr></tbody>';
}

// Pagination links
echo '<nav aria-label="Order Table Pagination"> 
        <ul class="pagination pagination-black justify-content-center">';

if ($page > 1) {
    echo '<li class="page-item">
            <a class="page-link" href="javascript:void(0)" onclick="filterOrdersByDate(' . ($page - 1) . ')" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
          </li>';
}

for ($i = 1; $i <= $total_pages; $i++) {
    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">
            <a class="page-link" href="javascript:void(0)" onclick="filterOrdersByDate(' . $i . ')">' . $i . '</a>
          </li>';
}

if ($page < $total_pages) {
    echo '<li class="page-item">
            <a class="page-link" href="javascript:void(0)" onclick="filterOrdersByDate(' . ($page + 1) . ')" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
          </li>';
}

echo '  </ul>
      </nav>';

// Close statement and connection
$stmt->close();
$conn->close();
?>
