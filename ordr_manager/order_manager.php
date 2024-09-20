<?php
session_start();
include '../includes/db_connect.php';

// Redirect to landing page if already logged in
if (isset($_SESSION['EmpLogExist']) && $_SESSION['EmpLogExist'] === true || isset($_SESSION['AdminLogExist']) && $_SESSION['AdminLogExist'] === true) {
    if (isset($_SESSION['emp_role'])) {
        // Redirect based on employee role
        switch ($_SESSION['emp_role']) {
            case 'Shipper':
                header("Location: ../shipper/shipper.php");
                exit;
            case 'Cashier':
                header("Location: ../cashier/cashier.php");
                exit;
            case 'Admin':
                header("Location: ../admin/admin_interface.php");
                exit;
            default:
                // Handle unknown roles or add default redirection if needed
                break;
        }
    }
} else {
    header("Location: ../login.php");
    exit;
}

// Query to fetch recent "Pending" orders
$pendingQuery = "
    SELECT o.order_id, o.prod_code, o.order_fullname, o.order_phonenum, p.prod_img,
           CONCAT(o.order_purok, ', ', o.order_barangay, ', ', o.order_province) AS order_address, 
           o.order_qty, o.order_total, o.order_date, p.prod_name, b.Brgy_df, p.prod_price, p.prod_discount,
           (o.order_total + 
               (CASE 
                   WHEN COUNT(0) OVER (PARTITION BY o.cust_id, o.order_date, o.order_barangay) = 1 
                   THEN b.Brgy_df 
                   ELSE ROUND(b.Brgy_df / COUNT(0) OVER (PARTITION BY o.cust_id, o.order_date, o.order_barangay), 2) 
                END)
           ) AS total_with_brgy_df
    FROM order_tbl o
    JOIN product_tbl p ON o.prod_code = p.prod_code
    JOIN brgy_tbl b ON o.order_barangay = b.Brgy_name
    WHERE o.status_code = 1 
      AND DATE(o.order_date) = CURDATE()
    ORDER BY o.order_date DESC";

$pendingResult = $conn->query($pendingQuery);


$processingQuery = "
    SELECT o.order_id, o.prod_code, o.order_fullname, o.order_phonenum, p.prod_img,
           CONCAT(o.order_purok, ', ', o.order_barangay, ', ', o.order_province) AS order_address, 
           o.order_qty, o.order_total, o.order_date, p.prod_name, b.Brgy_df, p.prod_price, p.prod_discount,
           (o.order_total + 
               (CASE 
                   WHEN COUNT(0) OVER (PARTITION BY o.cust_id, o.order_date, o.order_barangay) = 1 
                   THEN b.Brgy_df 
                   ELSE ROUND(b.Brgy_df / COUNT(0) OVER (PARTITION BY o.cust_id, o.order_date, o.order_barangay), 2) 
                END)
           ) AS total_with_brgy_df
    FROM order_tbl o
    JOIN product_tbl p ON o.prod_code = p.prod_code
    JOIN brgy_tbl b ON o.order_barangay = b.Brgy_name
    WHERE o.status_code = 2 
      AND DATE(o.order_date) = CURDATE()
    ORDER BY o.order_date DESC";

$processingResult = $conn->query($processingQuery);

// Fetch distinct brgy_route from brgy_tbl
$routeSql = "SELECT DISTINCT brgy_route FROM brgy_tbl ORDER BY brgy_route ASC";
$routeResult = $conn->query($routeSql);

// Initialize an array to store the counts for each status
$statusCounts = [
    'Pending' => 0,
    'Processing' => 0,
    'Shipped' => 0,
    'Delivered' => 0,
    'Canceled' => 0
];

// Query to get the count of orders for each status on the current day
$statusQuery = "
    SELECT s.status_name, COUNT(o.order_id) AS total_orders 
    FROM order_tbl o 
    JOIN status_tbl s ON o.status_code = s.status_code 
    WHERE DATE(o.order_date) = CURDATE() 
    GROUP BY s.status_name";

$statusResult = $conn->query($statusQuery);

// Check if $statusResult is valid before accessing num_rows
if ($statusResult) {
    while ($row = $statusResult->fetch_assoc()) {
        // Update the status counts only if the status name exists in the array
        if (array_key_exists($row['status_name'], $statusCounts)) {
            $statusCounts[$row['status_name']] = $row['total_orders'];
        }
    }
} else {
    // Handle query failure
    echo "Error: " . $conn->error;
}

// Query to get delivery personnel, total assigned orders, and progress status within the day
$transactQuery = "SELECT shipper_id, emp_tbl.emp_img, CONCAT(emp_tbl.emp_fname, ' ', emp_tbl.emp_lname) AS full_name,
                  COUNT(DISTINCT order_id) AS total_orders,
                  SUM(CASE WHEN transact_status = 'Success' THEN 1 ELSE 0 END) AS completed_orders,
                  SUM(CASE WHEN transact_status = 'Ongoing' THEN 1 ELSE 0 END) AS ongoing_orders,
                  SUM(CASE WHEN transact_status = 'Failed' THEN 1 ELSE 0 END) AS failed_orders,
                  DATE_FORMAT(transact_date, '%H:%i') AS transact_time
                  FROM delivery_transactbl
                  INNER JOIN emp_tbl ON delivery_transactbl.shipper_id = emp_tbl.emp_id
                  WHERE DATE(transact_date) = CURDATE()
                  GROUP BY shipper_id, transact_time
                  ORDER BY transact_time DESC";

$transactResult = mysqli_query($conn, $transactQuery);

$ongoingOrders = [];    // Orders that are still ongoing or in progress
$completedOrders = [];  // Orders that are fully completed
$failedOrders = [];     // Orders that failed delivery

if (mysqli_num_rows($transactResult) > 0) {
    while ($row = mysqli_fetch_assoc($transactResult)) {
        // Calculate progress percentage
        $progress = ($row['total_orders'] > 0) ? round(($row['completed_orders'] / $row['total_orders']) * 100) : 0;

        // Separate ongoing, completed, and failed orders
        if ($row['completed_orders'] == $row['total_orders']) {
            $completedOrders[] = [
                'shipper_id' => $row['shipper_id'],
                'full_name' => $row['full_name'],
                'total_orders' => $row['total_orders'],
                'completed_orders' => $row['completed_orders'],
                'progress' => $progress,
                'shipper_img' => $row['emp_img'],
            ];
        } elseif ($row['failed_orders'] > 0) {
            // Failed orders
            $failedOrders[] = [
                'shipper_id' => $row['shipper_id'],
                'full_name' => $row['full_name'],
                'total_orders' => $row['total_orders'],
                'failed_orders' => $row['failed_orders'],
                'progress' => 0, // Failed orders don't need a progress bar, they are considered 0% completed
                'shipper_img' => $row['emp_img'],
            ];
        } else {
            // Ongoing orders
            if ($row['ongoing_orders'] > 0) {
                $ongoingOrders[] = [
                    'shipper_id' => $row['shipper_id'],
                    'full_name' => $row['full_name'],
                    'total_orders' => $row['total_orders'],
                    'completed_orders' => $row['completed_orders'],
                    'progress' => $progress,
                    'shipper_img' => $row['emp_img'],
                ];
            }
        }
    }
}
// Close the database connection
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Manager</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />



    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.bootstrap5.css">



    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.js">

    <link rel="icon" href="../img/logo.ico" type="image/x-icon">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/ordr_css.css">
    <style>
        
.accBtn,
.shipBtn,
.trackBtn{
    background-color: #FF8225; 
    color: white;
    transition: background-color 0.3s ease-in-out;
    border: none;
}

.printAllBtn{
    background-color: #ec4242; 
    color: white;
    transition:background-color 0.3s ease-in-out;
    border: none;
}

.printAllBtn:hover{
    background-color: #c12e2e; 
    color: white;

}

.accBtn:hover,
.shipBtn:hover,
.trackBtn:hover{
    background-color: #c12e2e; 
    color: white;
}



.form-control:focus {
    border-color: #FF8225;
    box-shadow: 0 0 5px rgba(255, 130, 37, 0.8);
}

/* For Webkit browsers */
::-webkit-scrollbar {
width: 12px; /* Width of the scrollbar */
}

::-webkit-scrollbar-track {
background: #f1f1f1; /* Color of the track */
}

::-webkit-scrollbar-thumb {
background: linear-gradient(180deg, #ff83259b, #a72828);
border-radius: 10px; /* Rounded corners of the scrollbar thumb */
}

::-webkit-scrollbar-thumb:hover {
background: linear-gradient(380deg, #a72828, #343a40);
}

 /* Custom scrollbar for the table */
 .table-responsive::-webkit-scrollbar {
    width: 8px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background-color: #FF8225;
    border-radius: 4px;

}

.table-responsive::-webkit-scrollbar-track {
    background-color: #f1f1f1;
}

label{
    margin-bottom: 10px;
}

/* Custom CSS to make the tooltip small */
.tooltip-inner {
    font-size: 0.8rem; /* Adjust font size for smaller tooltip */
    padding: 0.5rem 0.5rem; /* Adjust padding for smaller tooltip */
    background-color: #414141;
    color: #fff; 
  }

  .tooltip-arrow {
    display: none; 
  }

.modal-content {
    background-color: #fff;
    color: #333;
}
.modal-header {
    background-color: #FF8225; /* Theme color */
    border-bottom: none;
}
.modal-footer {
    border-top: none;
}
.btn.confirm-btn {
    background-color: #a72828; /* Theme color */
    border-color: #a72828;
    color: #fff;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    border-radius: 0.25rem;
}
.btn-primary{
    background-color: #a72828; 
}
.btn.confirm-btn:hover,
.btn-primary {
    background-color: #8a1b1b; /* Darker shade for hover effect */
    border-color: #8a1b1b;
}
.btn.cancel-btn {
    background-color: #fff;
    color: #FF8225;
    border: 1px solid #FF8225;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    border-radius: 10px;
}
.btn.cancel-btn:hover {
    background-color: #FF8225;
    color: #fff;
}
.list_orders{
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-title{
    color: white;
}

.profile-container img{
    image-rendering: crisp-edges;
}

.active1{
    background: linear-gradient(180deg, #ff83259b, #a72828);
}


.DeclineBtn{
    background-color: #ec4242; 
    color: white;
    transition:background-color 0.3s ease-in-out;
    border: none;
}

.DeclineBtn:hover{
    background-color: #c12e2e; 
    color: white;

}
.product-img {
            width: 60px;
            height: auto;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .product-img+span {
            font-size: 14px;
            line-height: 1.5;
        }
    </style>

</head>

<body>

    <!-- Sidebar on the left -->
    <?php include '../includes/omSideBar.php'; ?>

    <!-- Main content -->
    <div class="content">
        <!-- Navbar for small screens -->
        <nav id="mobile-nav" class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Order Manager</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="#">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Customers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Reports</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Real-time clock -->
        <div id="clock-container">
            <div id="clock"></div>
            <div id="date"></div>
        </div>

        <hr>
        <!-- Status Cards Section -->
        <div class="container-fluid">
            <h2 class="my-4">Today Total Order Statuses</h2>
            <div class="status-container">
                <!-- Pending -->
                <div class="status-card bg-warning">
                    <i class="fas fa-clock"></i>
                    <div class="status-text">Pending</div>
                    <div class="status-number"><?php echo $statusCounts['Pending']; ?></div>
                </div>

                <!-- Processing -->
                <div class="status-card bg-primary">
                    <i class="fas fa-cogs"></i>
                    <div class="status-text">Processing</div>
                    <div class="status-number"><?php echo $statusCounts['Processing']; ?></div>
                </div>

                <!-- Shipped -->
                <div class="status-card bg-success">
                    <i class="fas fa-truck"></i>
                    <div class="status-text">Shipped</div>
                    <div class="status-number"><?php echo $statusCounts['Shipped']; ?></div>
                </div>

                <!-- Delivered -->
                <div class="status-card bg-info">
                    <i class="fas fa-check-circle"></i>
                    <div class="status-text">Delivered</div>
                    <div class="status-number"><?php echo $statusCounts['Delivered']; ?></div>
                </div>

                <!-- Canceled -->
                <div class="status-card bg-danger">
                    <i class="fas fa-times-circle"></i>
                    <div class="status-text">Canceled</div>
                    <div class="status-number"><?php echo $statusCounts['Canceled']; ?></div>
                </div>


            </div>
        </div>




        <hr>

        <!-- Recent Orders -->
        <div class="container-fluid mt-4">
            <h2 class="mb-4">Recent Pending Orders</h2>

            <!-- Checkbox, Input, and Button Section -->
            <div class="row g-2 mb-3 align-items-center">
                <div class="col-12 col-sm-auto">
                    <input type="number" id="processingNumber" placeholder="Enter Number" class="form-control" data-bs-toggle="tooltip" data-bs-placement="right" title="Enter # of accepting orders" />
                </div>
                <div class="col-12 col-sm-auto">
                    <button class="btn accBtn w-100 w-sm-auto" id="acceptBtn" data-bs-toggle="tooltip" data-bs-placement="right" title="Click to accept selected orders" style="color: #ffffff;">
                        <i class="fa fa-circle-check"></i> Accept Order(s)
                    </button>
                </div>
                  <!-- Decline button -->
                  <div class="col-12 col-sm-auto">
                    <button class="btn DeclineBtn w-100 w-sm-auto" id="cancellationBtn" data-bs-toggle="tooltip" data-bs-placement="right" title="Decline orders" style="color: #ffffff;">
                        <i class="fas fa-square-minus" style="color: #ffffff;"></i> Decline Order(s)
                    </button>
                </div>
            </div>


            <!-- Scrollable Table Wrapper -->
            <div class="table-responsive overflow-auto">
                <!-- Table -->
                <table class="table table-striped" id="ordersTable">
                    <thead>
                        <tr>
                            <th style="background-color: #ce3434bd; color: white;"><input type="checkbox" id="checkAll" data-bs-toggle="tooltip" data-bs-placement="right" title="Check All"></th>
                            <th style="background-color: #ce3434bd; color: white;">Order ID</th>
                            <th style="background-color: #ce3434bd; color: white;">Product</th>
                            <th style="background-color: #ce3434bd; color: white;">Full Name</th>
                            <th style="background-color: #ce3434bd; color: white;">Phone Number</th>
                            <th style="background-color: #ce3434bd; color: white;">Address</th>
                            <th style="background-color: #ce3434bd; color: white;">Unit Price (kg)</th>
                            <th style="background-color: #ce3434bd; color: white;">Delivery Fee</th>
                            <th style="background-color: #ce3434bd; color: white;">Quantity</th>
                            <th style="background-color: #ce3434bd; color: white;">Total</th>
                            <th style="background-color: #ce3434bd; color: white;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pendingResult->num_rows > 0): ?>
                            <?php while ($row = $pendingResult->fetch_assoc()): ?>
                                <tr>
                                    <td><input type="checkbox" class="orderCheckbox" value="<?php echo $row['order_id']; ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="Check to select Order"></td>
                                    <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center">
                                            <img src="../<?php echo htmlspecialchars($row['prod_img']);?>" alt="<?php echo htmlspecialchars($row['prod_name']);?>" class="product-img">
                                            <span class="ms-2"><?php echo htmlspecialchars($row['prod_name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['order_fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['order_phonenum']); ?></td>
                                    <td><?php echo htmlspecialchars($row['order_address']); ?></td>
                                    <?php if ($row['prod_discount'] > 0) { ?>
                                        <td><?php echo htmlspecialchars($row['prod_discount']); ?></td>
                                    <?php } else { ?>
                                        <td><?php echo htmlspecialchars($row['prod_price']); ?></td>
                                    <?php } ?>
                                    <td><?php echo htmlspecialchars($row['Brgy_df']); ?></td>
                                    <td><?php echo htmlspecialchars($row['order_qty']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($row['total_with_brgy_df'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(date('F j, Y g:i A', strtotime($row['order_date']))); ?></td>

                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>

                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>



        <hr>
        <!--Processing Orders-->
        <div class="container-fluid mt-4">
            <h2 class="mb-4">Processing Orders</h2>

            <!-- Checkbox, Input, and Button Section -->
            <div class="row g-2 mb-3 align-items-center">
    <!-- Dropdown filter -->
    <div class="col-12 col-sm-auto">
        <select id="orderFilter" class="form-select" onchange="applyFilter()" data-bs-toggle="tooltip" data-bs-placement="right" title="Filter orders">
            <option value="default">Filter by</option>
            <option value="az">A-Z</option>
            <option value="za">Z-A</option>

            <!-- Populate brgy_route dynamically -->
            <?php if ($routeResult->num_rows > 0): ?>
                <?php while ($row = $routeResult->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['brgy_route']); ?>">
                        <?php echo htmlspecialchars($row['brgy_route']); ?>
                    </option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>
    </div>

    <!-- Number input -->
    <div class="col-12 col-sm-auto">
        <input type="number" id="processOrderNumber" placeholder="Enter Number" class="form-control" data-bs-toggle="tooltip" data-bs-placement="right" title="Enter # to ship orders" />
    </div>

    <!-- Ship button -->
    <div class="col-12 col-sm-auto">
        <button class="btn shipBtn w-100 w-sm-auto" id="shipBtn" data-bs-toggle="tooltip" data-bs-placement="right" title="Click to ship selected orders" style="color: #ffffff; ">
            <i class="fa fa-truck" style="color: #ffffff;"></i> Ship Order(s)
        </button>
    </div>

    <!-- Print button -->
    <div class="col-12 col-sm-auto">
        <button class="btn printAllBtn w-100 w-sm-auto" id="printAllBtn" data-bs-toggle="tooltip" data-bs-placement="right" title="Print orders" style="color: #ffffff; ">
            <i class="fas fa-print" style="color: #ffffff;"></i> Print Order(s)
        </button>
    </div>
</div>


            <div class="table-responsive overflow-auto">
                <!--Table-->
                <table class="table table-striped" id="processingTable">
                    <thead>
                        <tr>
                            <th style="background-color: #ce3434bd; color: white;"><input type="checkbox" id="processingCheck" data-bs-toggle="tooltip" data-bs-placement="right" title="Check All"></th>
                            <th style="background-color: #ce3434bd; color: white;">Order ID</th>
                            <th style="background-color: #ce3434bd; color: white;">Product</th>
                            <th style="background-color: #ce3434bd; color: white;">Full Name</th>
                            <th style="background-color: #ce3434bd; color: white;">Phone Number</th>
                            <th style="background-color: #ce3434bd; color: white;">Address</th>
                            <th style="background-color: #ce3434bd; color: white;">Unit Price (kg)</th>
                            <th style="background-color: #ce3434bd; color: white;">Delivery Fee</th>
                            <th style="background-color: #ce3434bd; color: white;">Quantity</th>
                            <th style="background-color: #ce3434bd; color: white;">Total</th>
                            <th style="background-color: #ce3434bd; color: white;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($processingResult->num_rows > 0): ?>
                            <?php while ($row = $processingResult->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="processingCheckbox" value="<?php echo htmlspecialchars($row['order_id']); ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="Check to select Order">
                                    </td>
                                    <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center">
                                            <img src="../<?php echo htmlspecialchars($row['prod_img']);?>" alt="<?php echo htmlspecialchars($row['prod_name']);?>" class="product-img">
                                            <span class="ms-2"><?php echo htmlspecialchars($row['prod_name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['order_fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['order_phonenum']); ?></td>
                                    <td><?php echo htmlspecialchars($row['order_address']); ?></td>
                                    <?php if ($row['prod_discount'] > 0) { ?>
                                        <td><?php echo htmlspecialchars($row['prod_discount']); ?></td>
                                    <?php } else { ?>
                                        <td><?php echo htmlspecialchars($row['prod_price']); ?></td>
                                    <?php } ?>
                                    <td><?php echo htmlspecialchars($row['Brgy_df']); ?></td>
                                    <td><?php echo htmlspecialchars($row['order_qty']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($row['total_with_brgy_df'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(date('F j, Y g:i A', strtotime($row['order_date']))); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>

                        <?php endif; ?>

                    </tbody>
                </table>

            </div>
        </div>
        <hr>
        <!-- Order Delivery Tracking -->
        <div class="container-fluid mt-4">
            <h2 class="mb-4 text-center">Order Delivery Tracking</h2>

            <!-- Ongoing Orders Section -->
            <h3 class="mb-4 text-center">Ongoing Orders</h3>
            <?php if (!empty($ongoingOrders)): ?>
                <?php foreach ($ongoingOrders as $person): ?>
                    <div class="card mb-3 shadow-lg" style="background: url('../img/bg.png') no-repeat center; background-size: cover; object-fit:cover;">
                        <div class="row g-0">
                            <div class="col-md-4 d-flex justify-content-center align-items-center p-4">
                                <div class="profile-container">
                                    <img src="../<?php echo $person['shipper_img']; ?>" class="rounded-circle img-fluid" alt="Employee Profile" style="width: 150px; height: 150px;">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">Total Orders: <?php echo $person['total_orders']; ?></h5>
                                    <p class="card-text">Delivery Personnel: <strong><?php echo $person['full_name']; ?></strong></p>
                                    <p class="card-text">Current Status:
                                        <span class="badge" style="background: #a72828;">Ongoing Delivery</span>
                                    </p>
                                    <div class="progress mt-3" style="height: 20px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $person['progress']; ?>%;" aria-valuenow="<?php echo $person['progress']; ?>" aria-valuemin="0" aria-valuemax="100">
                                            <?php echo $person['progress']; ?>% Completed
                                        </div>
                                    </div>
                                    <button type="button" class="trackBtn btn mt-3" data-shipper-id="<?php echo $person['shipper_id']; ?>" style="color: white;">Track Delivery</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center" style="opacity: 0.65;"> <i class="fas fa-box" style="font-size: 50px; color: #495057; opacity: 0.65;"></i> <br> No ongoing orders found for today.</p>
            <?php endif; ?>

            <!-- Completed Orders Section -->
            <h3 class="mb-4 text-center" style="margin-top: 50px;">Completed Orders</h3>
            <?php if (!empty($completedOrders)): ?>
                <?php foreach ($completedOrders as $person): ?>
                    <div class="card mb-3 shadow-lg" style="background: url('../img/bg.png') no-repeat center; background-size: cover; object-fit:cover;">
                        <div class="row g-0">
                            <div class="col-md-4 d-flex justify-content-center align-items-center p-4">
                                <div class="profile-container">
                                    <img src="../<?php echo $person['shipper_img']; ?>" class="rounded-circle img-fluid" alt="Employee Profile" style="width: 150px; height: 150px;">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">Total Orders: <?php echo $person['total_orders']; ?></h5>
                                    <p class="card-text">Delivery Personnel: <strong><?php echo $person['full_name']; ?></strong></p>
                                    <p class="card-text">Current Status:
                                        <span class="badge" style="background: #a72828;">All Delivered</span>
                                    </p>
                                    <div class="progress mt-3" style="height: 20px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                                            100% Completed
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center" style="opacity: 0.65;"> <i class="fas fa-box" style="font-size: 50px; color: #495057; opacity: 0.65;"></i> <br> No completed orders found for today.</p>
            <?php endif; ?>

            <!-- Failed Delivery Attempts Section -->
            <h3 class="mb-4 text-center" style="margin-top: 50px;">Failed Delivery Attempt Orders</h3>
            <?php if (!empty($failedOrders)): ?>
                <?php foreach ($failedOrders as $person): ?>
                    <div class="card mb-3 shadow-lg" style="background: url('../img/bg.png') no-repeat center; background-size: cover; object-fit:cover;">
                        <div class="row g-0">
                            <div class="col-md-4 d-flex justify-content-center align-items-center p-4">
                                <div class="profile-container">
                                    <img src="../<?php echo $person['shipper_img']; ?>" class="rounded-circle img-fluid" alt="Employee Profile" style="width: 150px; height: 150px;">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">Total Orders: <?php echo $person['total_orders']; ?></h5>
                                    <p class="card-text">Delivery Personnel: <strong><?php echo $person['full_name']; ?></strong></p>
                                    <p class="card-text">Current Status:
                                        <span class="badge" style="background: #a72828;">Failed Delivery</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center" style="opacity: 0.65;"> <i class="fas fa-box" style="font-size: 50px; color: #495057; opacity: 0.65;"></i> <br> No failed delivery attempt orders found for today.</p>
            <?php endif; ?>
        </div>

        <hr>



        <!-- Order Cards -->
        <div class="container-fluid">
            <h1 class="my-4">Orders</h1>
            <div class="row g-3">
                <div class="col-6 col-md-3 mb-1">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Order #12345</h5>
                            <p class="card-text">Customer: John Doe</p>
                            <p class="card-text">Status: <span class="badge bg-success">Shipped</span></p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#orderModal">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>


                <!-- More cards can be added here -->
            </div>
        </div>

    </div> <!--End of Container-->

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderModalLabel">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Order #12345</p>
                    <p>Customer: John Doe</p>
                    <p>Items: 3</p>
                    <p>Total: $200</p>
                    <p>Status: Shipped</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="shipModal" tabindex="-1" aria-labelledby="shipModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shipModalLabel">Confirm Shipping</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Emp ID (Dropdown) -->
                    <div class="mb-3">
                        <label for="empId" class="form-label">Employee</label>
                        <select class="form-select" id="empId">
                            <option selected disabled>Select Employee</option>
                            <!-- Options will be dynamically loaded from the database -->
                        </select>
                    </div>

                    <!-- Order IDs (readonly, based on selection) -->
                    <div class="mb-3">
                        <label for="orderId" class="form-label">Order IDs</label>
                        <input type="text" class="form-control" id="orderId" readonly />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmShip" style="background-color: #a72828; border:#a72828;"> <i class="fa fa-truck" style="color: #ffffff;"></i> Ship Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Official Receipt Confirmation -->
    <div class="modal fade" id="officialReceiptModal" tabindex="1" aria-labelledby="officialReceiptLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0" style="border-radius: 0.5rem;">
                <div class="modal-header text-white">
                    <h5 class="modal-title" id="officialReceiptLabel">Confirm Print Order Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-center">Are you sure you want to print the Order Receipt for the selected orders?</p>
                    <!-- Display list of orders in modal -->
                    <ul id="selectedOrdersList" class="list-group list-group-flush">

                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn cancel-btn" data-bs-dismiss="modal"> <i class="fa-regular fa-rectangle-xmark"></i> Cancel</button>
                    <button type="button" class="btn confirm-btn" id="confirmPrintBtn"> <i class="fa-solid fa-check-double"></i> Confirm Print</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Track Delivery Modal -->
    <div class="modal fade" id="trackDeliveryModal" tabindex="-1" aria-labelledby="trackDeliveryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="trackDeliveryModalLabel">Delivery Transaction Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Order details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

      <!-- Decline Order Modal -->
<div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="declineModalLabel">Decline Selected Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Full Name</th>
                        </tr>
                    </thead>
                    <tbody id="declineOrderTable">
                        <!-- Selected orders will be populated here via JS -->
                    </tbody>
                </table>
                <div class="mb-3">
                    <label for="declineReason" class="form-label">Reason for Declining</label>
                    <textarea class="form-control" id="declineReason" rows="3" placeholder="Please enter a reason"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDeclineBtn" class="btn btn-danger">Confirm Decline</button>
            </div>
        </div>
    </div>
</div>



    <!-- DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/order_manager.js"></script>
    <!-- Include DataTables JS -->

    <script>
        $(document).ready(function() {
            $('.trackBtn').on('click', function() {
                var shipperId = $(this).data('shipper-id'); // Get shipper_id from button data attribute
                console.log("Shipper ID: " + shipperId); // Debugging line to check shipper_id
                $.ajax({
                    url: 'fetch_order_details.php',
                    type: 'POST',
                    data: {
                        shipper_id: shipperId
                    },
                    success: function(response) {
                        $('#modalBody').html(response);
                        $('#trackDeliveryModal').modal('show');
                    }
                });
            });
        });

        $(document).ready(function() {
    let selectedOrders = [];

    // Check All functionality
    $('#checkAll').on('change', function() {
        var checkboxes = $('.orderCheckbox');
        checkboxes.prop('checked', this.checked);
    });

    // Decline Button - Open Modal and Populate Data
    $('#cancellationBtn').on('click', function() {
        selectedOrders = [];
        const checkboxes = $('.orderCheckbox:checked');

        // Validate selected orders
        if (checkboxes.length === 0) {
            Swal.fire('Warning', 'Please select orders to decline.', 'warning');
            return;
        }

        // Populate modal table with selected orders
        $('#declineOrderTable').empty();
        checkboxes.each(function() {
            const orderRow = $(this).closest('tr');
            const orderId = orderRow.find('td:eq(1)').text();
            const productName = orderRow.find('td:eq(2)').text();
            const fullName = orderRow.find('td:eq(3)').text();

            $('#declineOrderTable').append(`
                <tr>
                    <td>${orderId}</td>
                    <td>${productName}</td>
                    <td>${fullName}</td>
                </tr>
            `);

            selectedOrders.push({
                order_id: $(this).val(),
                prod_name: productName,
                order_fullname: fullName
            });
        });

        // Open the modal
        $('#declineModal').modal('show');
    });

    // Confirm Decline - Send Data to Server
    $('#confirmDeclineBtn').on('click', function() {
        const reason = $('#declineReason').val().trim();
        
        if (!reason) {
            Swal.fire('Warning', 'Please provide a reason for declining.', 'warning');
            return;
        }

        // Send declined orders and reason to the server via AJAX
        fetch('decline_orders.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                orders: selectedOrders,
                reason: reason
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success',
                    text: 'Orders successfully declined!',
                    icon: 'success'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire('Error', 'Failed to decline orders: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error); // Log the error for debugging
            Swal.fire('Error', 'An error occurred while declining orders.', 'error');
        });
    });
});

    </script>
</body>

</html>