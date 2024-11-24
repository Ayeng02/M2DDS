<?php
// Include database connection
include '../includes/db_connect.php'; // Adjust path as necessary

// Get the selected date and pagination parameters
$selectedDate = $_GET['transac_date'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

// Fetch total number of orders for pagination
$count_sql = "SELECT COUNT(*) AS total 
              FROM pos_tbl p
              JOIN emp_tbl e ON p.pos_personnel = e.emp_id
              JOIN product_tbl pp ON p.prod_code = pp.prod_code
              WHERE p.transac_date LIKE '%$selectedDate%'";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch the filtered orders
$sql = "SELECT p.pos_code, pp.prod_name, p.pos_qty, 
         p.pos_discount, p.total_amount, p.amount_received, p.pos_change, CONCAT(e.emp_fname, '' , e.emp_lname) as fullname, p.transac_date
        FROM pos_tbl p
        JOIN emp_tbl e ON p.pos_personnel = e.emp_id
        JOIN product_tbl pp ON p.prod_code = pp.prod_code
        WHERE p.transac_date LIKE '%$selectedDate%'
        LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

// Output the table rows for the orders
if ($result->num_rows > 0) {
    echo '<tbody id="order-rows">'; // Start table body marker
    while ($pos = $result->fetch_assoc()) {
        echo '<tr>
                <td>' . htmlspecialchars($pos['pos_code']) . '</td>
                <td>' . htmlspecialchars($pos['prod_name']) . '</td>
                <td>' . htmlspecialchars($pos['pos_qty']) . '</td>
                <td>' . htmlspecialchars($pos['pos_discount']) . '</td>
                <td>' . htmlspecialchars($pos['total_amount']) . '</td>
                <td>' . htmlspecialchars(number_format($pos['amount_received'])) . '</td>
                <td>' . htmlspecialchars(number_format($pos['pos_change'])) . '</td>
                <td>' . htmlspecialchars($pos['fullname']) . '</td>
                <td>' . date('F j, Y h:i A', strtotime($pos['transac_date'])) .  '</td>
                
                <td>
                    <a href="#" class="edit-icon" data-bs-toggle="modal" data-bs-target="#editModal"
                        data-pos-id="' . htmlspecialchars($pos['pos_code']) . '"
                        data-prod-name="' . htmlspecialchars($pos['prod_name']) . '"
                        data-qty="' . htmlspecialchars($pos['pos_qty']) . '"
                        data-discount="' . htmlspecialchars($pos['pos_discount']) . '"
                        data-total="' . htmlspecialchars($pos['total_amount']) . '"
                        data-recieve="' . htmlspecialchars(number_format($pos['amount_received'])) . '"
                        data-change="' . htmlspecialchars(number_format($pos['pos_change'])) . '"
                        data-fullname="' . htmlspecialchars($pos['fullname']) . '"
                        data-date="' . date('F j, Y h:i A', strtotime($pos['transac_date'])) . '"
                       >
                        <i class="fa fa-edit"></i>
                    </a>
                    <a href="delete_pos.php?id=' . htmlspecialchars($pos['pos_code']) . '" class="delete-icon" onclick="confirmDelete(event, \'' . htmlspecialchars($pos['pos_code']) . '\')">
                        <i class="fa fa-trash"></i>
                    </a>
                </td>
              </tr>';
    }
    echo '</tbody>'; // End table body marker
} else {
    echo '<tbody id="order-rows"><tr><td colspan="11">No transaction found for this date.</td></tr></tbody>';
}

// Pagination links
echo '<nav aria-label="Order Table Pagination"> 
        <ul class="pagination pagination-black justify-content-center">';

if ($page > 1) {
    echo '<li class="page-item">
            <a class="page-link" href="javascript:void(0)" onclick="filterTransactByDate(' . ($page - 1) . ')" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
          </li>';
}

for ($i = 1; $i <= $total_pages; $i++) {
    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">
            <a class="page-link" href="javascript:void(0)" onclick="filterTransactByDate(' . $i . ')">' . $i . '</a>
          </li>';
}

if ($page < $total_pages) {
    echo '<li class="page-item">
            <a class="page-link" href="javascript:void(0)" onclick="filterTransactByDate(' . ($page + 1) . ')" aria-label="Next">
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
