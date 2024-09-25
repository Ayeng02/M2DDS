<?php
include '../includes/db_connect.php';

$category_code = $_GET['category_code'] ?? '';

// Prepare the query to fetch products from the selected category
$sql = "SELECT p.prod_code, c.category_name, p.prod_name, p.prod_price, p.prod_discount, p.prod_qoh, p.prod_img 
        FROM product_tbl p 
        JOIN category_tbl c ON p.category_code = c.category_code
        WHERE p.category_code = '$category_code'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['prod_code']}</td>
                <td contenteditable='true'>{$row['category_name']}</td>
                 <td>
                    <img src='" . htmlspecialchars($row['prod_img']) . "' alt='Product Image' style='width: 120px; height: 50px;'>
                </td>
                <td contenteditable='true'>{$row['prod_name']}</td>
                <td contenteditable='true'>" . number_format($row['prod_price']) . "</td>
                <td contenteditable='true'>" . number_format($row['prod_qoh']) . "</td>
                <td contenteditable='true'>" . number_format($row['prod_discount']) . "</td>
               

                <td>
                    <!-- Edit icon -->
                    <a href='#' class='edit-icon' data-bs-toggle='modal' data-bs-target='#editModal'
                        data-prod-code='{$row['prod_code']}'
                        data-category-name='{$row['category_name']}'
                        data-prod-name='{$row['prod_name']}'
                        data-prod-price='" . number_format($row['prod_price']) . "'
                        data-prod-qoh='" . number_format($row['prod_qoh']) . "'
                        data-prod-discount='" . number_format($row['prod_discount']) . "'>
                        
                        <i class='fa fa-edit'></i>
                    </a>
                    <!-- Delete icon -->
                    <a href='delete_products.php?id={$row['prod_code']}' class='delete-icon' onclick=\"confirmDelete(event, '{$row['prod_code']}')\">
                        <i class='fa fa-trash'></i>
                    </a>
                </td>
                
              </tr>";
    }
} else {
    echo "<tr><td colspan='7'>No products found in this category.</td></tr>";
}
?>
