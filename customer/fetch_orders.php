<?php
session_start();
$cust_id = $_SESSION['cust_id'];

include '../includes/db_connect.php';

// Retrieve parameters
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : ''; // New date parameter
$view = isset($_GET['view']) ? $_GET['view'] : 'grid'; // Retrieve view type parameter

$limit = 9;
$offset = ($page - 1) * $limit;

// Build query
$status_query = $status !== 'all' ? "AND status_name = '$status'" : "";
$search_query = $search ? "AND (order_id LIKE '%$search%' || order_fullname LIKE '%$search%' )" : "";
$date_query = $date ? "AND DATE(order_date) = '$date'" : "";  // New date filter query

$query = "
    SELECT o.*, s.status_name, p.prod_name, b.Brgy_Name, b.Brgy_df
    FROM order_tbl o
    JOIN status_tbl s ON o.status_code = s.status_code
    JOIN product_tbl p ON o.prod_code = p.prod_code
    JOIN brgy_tbl b ON o.order_barangay = b.Brgy_Name
    WHERE o.cust_id = '$cust_id' $status_query $search_query $date_query
    ORDER BY o.order_date DESC
    LIMIT $limit OFFSET $offset
";

$result = mysqli_query($conn, $query);

// Generate orders HTML
$orders = '';
while ($order = mysqli_fetch_assoc($result)) {
    $orders .= generateOrderCard($order);
}

// Pagination
$total_query = "
    SELECT COUNT(*) as total
    FROM order_tbl o
    JOIN status_tbl s ON o.status_code = s.status_code
    WHERE o.cust_id = '$cust_id' $status_query $search_query $date_query
";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_orders = $total_row['total'];
$total_pages = ceil($total_orders / $limit);

$pagination = '<ul class="pagination">';

$max_pages_to_show = 5;
$start_page = max(1, $page - intval($max_pages_to_show / 2));
$end_page = min($total_pages, $start_page + $max_pages_to_show - 1);

// Adjust start page if we're near the end
if ($end_page - $start_page < $max_pages_to_show - 1) {
    $start_page = max(1, $end_page - $max_pages_to_show + 1);
}

// "Previous" button
if ($page > 1) {
    $pagination .= '<li class="page-item"><a class="page-link" href="#" onclick="loadOrders(\'' . $status . '\', ' . ($page - 1) . ')">Previous</a></li>';
}

// Page numbers
for ($i = $start_page; $i <= $end_page; $i++) {
    $activeClass = $i == $page ? 'active' : '';  // Use double equals (==) for comparison
    $pagination .= '<li class="page-item ' . $activeClass . '">
                        <a class="page-link" href="#" onclick="loadOrders(\'' . $status . '\', ' . $i . ')">' . $i . '</a>
                    </li>';
}

// "Next" button
if ($page < $total_pages) {
    $pagination .= '<li class="page-item"><a class="page-link" href="#" onclick="loadOrders(\'' . $status . '\', ' . ($page + 1) . ')">Next</a></li>';
}

$pagination .= '</ul>';

$response = [
    'orders' => $orders,
    'pagination' => $pagination
];

echo json_encode($response);

function generateOrderCard($order) {
    $cancelButton = '';
    $orderButton = '';
    if ($order['status_name'] === 'Pending' || $order['status_name'] === 'Processing') {
        $cancelButton = '<button class="btn btn-danger cancel-btn" onclick="cancelOrder(\'' . htmlspecialchars($order['order_id']) . '\')">Cancel Order</button>';
        
    }else if ($order['status_name'] === 'Canceled'){
        $orderButton = '<a href="product-details.php?id=' . htmlspecialchars($order['prod_code']) . '" class="btn btn-primary mt-2" style="background-color: #FF5349; border-color: #FF5349; border-radius: 10px;"> <i class="fas fa-cart-arrow-down"></i>  Order</a>';
    }

     // Calculate total including delivery fee
     $totalWithDelivery = floatval($order['order_total']) + floatval($order['Brgy_df']);
    
    return '
        <div class="col-md-4 mb-3" id="order-card-' . htmlspecialchars($order['order_id']) . '">
            <div class="card">
                <div class="card-header">
                    Order ID: ' . htmlspecialchars($order['order_id']) . '
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> ' . htmlspecialchars($order['order_fullname']) . '</p>
                    <p><strong>Phone:</strong> ' . htmlspecialchars($order['order_phonenum']) . '</p>
                    <p><strong>Address:</strong> ' . htmlspecialchars($order['order_barangay']) . ', ' . htmlspecialchars($order['order_purok']) . ', ' . htmlspecialchars($order['order_province']) . '</p>
                    <p><strong>Product Name:</strong> ' . htmlspecialchars($order['prod_name']) . '</p>
                    <p><strong>Quantity:</strong> ' . htmlspecialchars($order['order_qty']) . '</p>
                    <p><strong>Total:</strong> ' . htmlspecialchars($totalWithDelivery) . '</p>
                    <button class="btn btn-view-details" onclick="toggleDetails(\'' . htmlspecialchars($order['order_id']) . '\')">More Details</button>
                    <div id="order-info-' . htmlspecialchars($order['order_id']) . '" class="order-info mt-3">
                        <p><strong>Cash Payment:</strong> ' . htmlspecialchars($order['order_cash']) . '</p>
                        <p><strong>Change:</strong> ' . htmlspecialchars($order['order_change']) . '</p>
                        <p><strong>Date:</strong> ' . htmlspecialchars($order['order_date']) . '</p>
                        <p><strong>Status:</strong> <span class="order-status">' . htmlspecialchars($order['status_name']) . '</span></p>
                        ' . $cancelButton . '
                        ' .$orderButton . '
                        
                    </div>
                    <a href="order-details.php?order_id=' . htmlspecialchars($order['order_id']) . '" class="btn btn-primary mt-3" style="margin-bottom:15px;">Track Order</a>
                </div>
            </div>
        </div>';
}

?>
