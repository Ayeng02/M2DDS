<?php
include '../includes/db_connect.php';

$status_code = $_GET['status_code'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

// Fetch total number of orders for pagination
$count_sql = "SELECT COUNT(*) AS total 
              FROM order_tbl o
              JOIN status_tbl s ON o.status_code = s.status_code
              WHERE s.status_code LIKE '%$status_code%'";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch the filtered orders
$sql = "SELECT o.order_id, o.order_fullname, o.order_phonenum, 
        CONCAT(o.order_purok, ' ', o.order_barangay, ', ', o.order_province) AS order_address,
        o.order_mop, o.order_qty, o.order_total, o.order_cash, o.order_change, s.status_name 
        FROM order_tbl o
        JOIN status_tbl s ON o.status_code = s.status_code
        WHERE s.status_code LIKE '%$status_code%'
        LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

// Output the table rows for the orders
if ($result->num_rows > 0) {
    echo '<tbody id="order-rows">'; // Start table body marker
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['order_id']}</td>
                <td contenteditable='true'>{$row['order_fullname']}</td>
                <td contenteditable='true'>{$row['order_phonenum']}</td>
                <td>{$row['order_address']}</td>
                <td>{$row['order_mop']}</td>
                <td contenteditable='true'>" . number_format($row['order_qty']) . "</td>
                <td contenteditable='true'>" . number_format($row['order_total']) . "</td>
                <td contenteditable='true'>" . number_format($row['order_cash']) . "</td>
                <td contenteditable='true'>" . number_format($row['order_change']) . "</td>
                <td contenteditable='true'>{$row['status_name']}</td>
                <td>
                    <a href='#' class='edit-icon' data-bs-toggle='modal' data-bs-target='#editModal'
                        data-order-id='{$row['order_id']}'
                        data-order-fullname='{$row['order_fullname']}'
                        data-order-phonenum='{$row['order_phonenum']}'
                        data-order-address='{$row['order_address']}'
                        data-order-mop='{$row['order_mop']}'
                        data-order-qty='" . number_format($row['order_qty']) . "'
                        data-order-total='" . number_format($row['order_total']) . "'
                        data-order-cash='" . number_format($row['order_cash']) . "'
                        data-order-change='" . number_format($row['order_change']) . "'
                        data-status-name='{$row['status_name']}'>
                        <i class='fa fa-edit'></i>
                    </a>
                    <a href='delete_orders.php?id={$row['order_id']}' class='delete-icon' onclick=\"confirmDelete(event, '{$row['order_id']}')\">
                        <i class='fa fa-trash'></i>
                    </a>
                </td>
              </tr>";
    }
    echo '</tbody>'; // End table body marker
} else {
    echo '<tbody id="order-rows"><tr><td colspan="11">No orders found for this status.</td></tr></tbody>';
}

// Pagination links
echo '<nav aria-label="Order Table Pagination"> 
        <ul class="pagination pagination-black justify-content-center">';

if ($page > 1) {
    echo '<li class="page-item">
            <a class="page-link" href="javascript:void(0)" onclick="sortTableStatus(' . ($page - 1) . ')" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
          </li>';
}

for ($i = 1; $i <= $total_pages; $i++) {
    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">
            <a class="page-link" href="javascript:void(0)" onclick="sortTableStatus(' . $i . ')">' . $i . '</a>
          </li>';
}

if ($page < $total_pages) {
    echo '<li class="page-item">
            <a class="page-link" href="javascript:void(0)" onclick="sortTableStatus(' . ($page + 1) . ')" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
          </li>';
}

echo '  </ul>
      </nav>';

