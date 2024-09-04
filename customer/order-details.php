<?php
include '../includes/db_connect.php'; // Ensure your database connection file is included

// Get the order ID from the URL
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    echo "<p class='text-danger'>Order ID is not specified.</p>";
    exit();
}

// Fetch order details
$sql = "SELECT 
            o.order_id,
            o.order_fullname,
            o.order_phonenum,
            o.order_barangay,
            o.order_purok,
            o.order_province,
            o.order_mop,
            o.order_total,
            o.order_date,
            s.status_code,
            s.status_name AS status
        FROM 
            Order_tbl o
        JOIN 
            status_tbl s ON o.status_code = s.status_code
        WHERE 
            o.order_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("<p class='text-danger'>Preparation failed: " . $conn->error . "</p>");
}

$stmt->bind_param("s", $order_id); // Use 's' for VARCHAR type
$stmt->execute();
$order_result = $stmt->get_result();

if (!$order_result || $order_result->num_rows === 0) {
    die("<p class='text-danger'>Order not found.</p>");
}

$order = $order_result->fetch_assoc();

// Fetch order items
$sql_items = "SELECT 
                    p.prod_name,
                    o.order_qty,
                    p.prod_price,
                    o.order_total,
                    p.prod_discount
                   
              FROM 
                    Order_tbl o
              JOIN 
                    product_tbl p ON o.prod_code = p.prod_code
              WHERE 
                    o.order_id = ?";

$stmt_items = $conn->prepare($sql_items);
if (!$stmt_items) {
    die("<p class='text-danger'>Preparation failed: " . $conn->error . "</p>");
}

$stmt_items->bind_param("s", $order_id); // Use 's' for VARCHAR type
$stmt_items->execute();
$items_result = $stmt_items->get_result();

// Fetch order status log
$sql_log = "SELECT 
                osl.change_date,
                osl.old_status,
                osl.new_status,
                s_old.status_name AS old_status_name,
                s_new.status_name AS new_status_name
            FROM 
                OrderStatusLog osl
            JOIN 
                status_tbl s_old ON osl.old_status = s_old.status_code
            JOIN 
                status_tbl s_new ON osl.new_status = s_new.status_code
            WHERE 
                osl.order_id = ?
            ORDER BY 
                osl.change_date DESC";

$stmt_log = $conn->prepare($sql_log);
if (!$stmt_log) {
    die("<p class='text-danger'>Preparation failed: " . $conn->error . "</p>");
}

$stmt_log->bind_param("s", $order_id); // Use 's' for VARCHAR type
$stmt_log->execute();
$log_result = $stmt_log->get_result();

$stmt->close();
$stmt_items->close();
$stmt_log->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background-color: #a72828;
            color: white;
        }

        .card-body {
            background-color: #ffffff;
        }

        .badge-custom {
            background-color: #FF8225;
            color: #ffffff;
        }

        .status-canceled {
            background-color: #dc3545;
            color: #ffffff;
        }

        .order-details-table th,
        .order-details-table td {
            vertical-align: middle;
            transition: background-color 0.3s;
        }

        .order-details-table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }

        .modal-content {
            border-radius: 0.5rem;
            animation: fadeIn 0.5s;
        }

        .btn-custom {
            background-color: #a72828;
            color: #ffffff;
        }

        .btn-custom:hover {
            background-color: #FF8225;
        }

        .progress-bar-custom {
            background-color: #FF8225;
        }

        .progress-bar-green {
            background-color: #28a745;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-4">Order Details</h1>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Order #<?php echo htmlspecialchars($order['order_id']); ?></h5>
            </div>
            <div class="card-body">
                <div class="order-info mb-4">
                    <p class="card-text"><strong>Status:</strong>
                        <span class="badge badge-custom <?php echo $order['status_code'] == 5 ? 'status-canceled' : ''; ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>
                    </p>
                    <p class="card-text"><strong>Customer:</strong> <?php echo htmlspecialchars($order['order_fullname']); ?></p>
                    <p class="card-text"><strong>Phone Number:</strong> <?php echo htmlspecialchars($order['order_phonenum']); ?></p>
                    <p class="card-text"><strong>Address:</strong> <?php echo htmlspecialchars($order['order_barangay']) . ', ' . htmlspecialchars($order['order_purok']) . ', ' . htmlspecialchars($order['order_province']); ?></p>
                    <p class="card-text"><strong>Mode of Payment:</strong> <?php echo htmlspecialchars($order['order_mop']); ?></p>
                    <p class="card-text"><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
                    <p class="card-text"><strong>Total:</strong> ₱<?php echo number_format($order['order_total'], 2); ?></p>

                    <?php if ($order['status_code'] == 5): ?>
                        <div class="alert alert-danger mt-4">
                            <h4 class="alert-heading">Order Canceled</h4>
                            <p>You canceled this order!</p>
                        </div>
                    <?php endif; ?>
                </div>
                <hr>
                <h5 class="mt-4">Order Items</h5>
                <div class="table-responsive">
                    <table id="orderItemsTable" class="table table-striped order-details-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($items_result->num_rows > 0) {
                                while ($item = $items_result->fetch_assoc()) {
                                    // Determine the unit price to display
                                    $unit_price = ($item['prod_discount'] > 0) ? $item['prod_discount'] : $item['prod_price'];
                                    $total_price = $unit_price * $item['order_qty']; // Calculate the total price per item

                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($item['prod_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($item['order_qty']) . "</td>";
                                    echo "<td>₱" . number_format($unit_price, 2) . "</td>";
                                    echo "<td>₱" . number_format($total_price, 2) . "</td>"; // Display the calculated total price
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>No items found.</td></tr>";
                            }
                            ?>
                        </tbody>

                    </table>
                </div>

                <hr>

                 <!-- Order Status Log Section -->
                <h5 class="mt-4">Order Status History</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Old Status</th>
                                <th>New Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($log_result->num_rows > 0) {
                                while ($log = $log_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . date('F j, Y, g:i a', strtotime($log['change_date'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($log['old_status_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($log['new_status_name']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center'>No status changes recorded.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>          

                <hr>
                <!-- Order Status Tracker -->
                <h5 class="mt-4">Track Order Status</h5>
                <div class="progress mb-4">
                    <?php
                    $statuses = [
                        1 => 'Pending',
                        2 => 'Processing',
                        3 => 'Shipped',
                        4 => 'Delivered',
                        5 => 'Canceled'
                    ];

                    $current_status = $order['status_code'];
                    $progress = ($current_status == 4) ? 100 : (array_search($current_status, array_keys($statuses)) + 1) * 20;
                    $progress_class = ($current_status == 4) ? 'progress-bar-green' : 'progress-bar-custom';
                    ?>

                    <div class="progress-bar <?php echo $progress_class; ?>" role="progressbar" style="width: <?php echo $progress; ?>%;" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
                        <?php echo $statuses[$current_status]; ?>
                    </div>
                </div>
                <ul class="list-group mb-4">
                    <?php if ($current_status != 5): ?>
                        <?php foreach ($statuses as $code => $name): ?>
                            <?php if ($code != 5 || !in_array($current_status, [3, 4])): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $name; ?>
                                    <span class="badge <?php echo $code < $current_status ? 'badge-primary' : ($code == $current_status ? 'badge-custom' : 'badge-secondary'); ?>">
                                        <?php echo $code < $current_status ? 'Completed' : ($code == $current_status ? 'Current' : 'Pending'); ?>
                                    </span>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Canceled
                            <span class="badge badge-danger">Canceled</span>
                        </li>
                    <?php endif; ?>
                    <a href="javascript:history.back()" class="btn btn-custom mb-4" style="margin-top: 20px;">Back</a>
                </ul>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#orderItemsTable').DataTable({
                "paging": false,
                "searching": false
            });
        });
    </script>
</body>

</html>