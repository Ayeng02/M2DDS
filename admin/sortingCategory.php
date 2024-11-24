<?php
include '../includes/db_connect.php';

$category_code = $_GET['category_code'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6; // Set the number of products per page
$offset = ($page - 1) * $limit;

// Fetch total number of products for pagination
$count_sql = "SELECT COUNT(*) AS total 
              FROM product_tbl p 
              WHERE p.category_code = '$category_code'";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch the filtered products
$sql = "SELECT p.prod_code, c.category_name, p.prod_name, p.prod_price, p.prod_discount, p.prod_qoh, p.prod_img 
        FROM product_tbl p 
        JOIN category_tbl c ON p.category_code = c.category_code
        WHERE p.category_code = '$category_code'
        LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

// Output the table rows for the products
if ($result->num_rows > 0) {
    echo '<tbody id="product-rows">'; // Start table body marker
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['prod_code']}</td>
                <td contenteditable='true'>{$row['category_name']}</td>
                <td>
                    <img src='../" . htmlspecialchars($row['prod_img']) . "' alt='Product Image' style='width: 120px; height: 50px;'>
                </td>
                <td contenteditable='true'>{$row['prod_name']}</td>
                <td contenteditable='true'>" . number_format($row['prod_price']) . "</td>
                <td contenteditable='true'>" . number_format($row['prod_qoh']) . "</td>
                <td contenteditable='true'>" . number_format($row['prod_discount']) . "</td>
                <td></td>
                <td>
                    <a href='#' class='edit-icon' data-bs-toggle='modal' data-bs-target='#editModal'
                        data-prod-code='{$row['prod_code']}'
                        data-category-name='{$row['category_name']}'
                        data-prod-name='{$row['prod_name']}'
                        data-prod-price='" . number_format($row['prod_price']) . "'
                        data-prod-qoh='" . number_format($row['prod_qoh']) . "'
                        data-prod-discount='" . number_format($row['prod_discount']) . "'>
                        <i class='fa fa-edit'></i>
                    </a>
                    <a href='delete_products.php?id={$row['prod_code']}' class='delete-icon' onclick=\"confirmDelete(event, '{$row['prod_code']}')\">
                        <i class='fa fa-trash'></i>
                    </a>
                </td>
              </tr>";
    }
    echo '</tbody>'; // End table body marker
} else {
    echo '<tbody id="product-rows"><tr><td colspan="8">No products found in this category.</td></tr></tbody>';
}

// Pagination links
echo '<nav aria-label="Page navigation"> 
        <ul class="pagination pagination-black justify-content-center">';

if ($page > 1) {
    echo '<li class="page-item">
            <a class="page-link" href="javascript:void(0)" onclick="sortTableCategory(' . ($page - 1) . ')" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
          </li>';
}

for ($i = 1; $i <= $total_pages; $i++) {
    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">
            <a class="page-link" href="javascript:void(0)" onclick="sortTableCategory(' . $i . ')">' . $i . '</a>
          </li>';
}

if ($page < $total_pages) {
    echo '<li class="page-item">
            <a class="page-link" href="javascript:void(0)" onclick="sortTableCategory(' . ($page + 1) . ')" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
          </li>';
}

echo '  </ul>
      </nav>';
?>
