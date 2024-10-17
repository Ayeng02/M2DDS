<?php
session_start();
// Set error reporting to ignore notices
error_reporting(E_ALL & ~E_NOTICE);
include '../includes/db_connect.php';

// Handle logout request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    if (session_id()) {
        session_destroy();
    }

    // Respond with a status to inform the JS the session has been destroyed
    exit;
}

// SQL query to retrieve product names, quantity on hand, and price
$quantity_sql = "SELECT prod_name, prod_qoh, prod_price 
                 FROM product_tbl
                 ORDER BY prod_qoh DESC";

$quantity_result = $conn->query($quantity_sql);

$products = [];
$quantities = [];
$prices = []; // Array to store product prices

// Fetch data and store product names, quantities, and prices in arrays
if ($quantity_result->num_rows > 0) {
    while ($row = $quantity_result->fetch_assoc()) {
        $products[] = $row['prod_name'];
        $quantities[] = $row['prod_qoh'];
        $prices[] = $row['prod_price']; // Store product prices
    }
} else {
    $products = [];
    $quantities = [];
    $prices = []; // Empty array if no results
}





$sale_sql = "SELECT SUM(order_total) AS daily_sales 
             FROM order_tbl 
             WHERE DATE(order_date) = CURDATE()
               AND status_code = '4'";
$sale_result = $conn->query($sale_sql);

$daily_sales = 0; // Initialize as 0 to avoid null

if ($sale_result->num_rows > 0) {
    $row = $sale_result->fetch_assoc();
    $daily_sales = $row['daily_sales'] ?? 0; // Use null coalescing operator to avoid null
}




$deliv_sql = "SELECT COUNT(*) AS total_deliv 
              FROM order_tbl 
              WHERE MONTH(order_date) = MONTH(CURDATE()) 
                AND YEAR(order_date) = YEAR(CURDATE()) 
                AND status_code = '4'";  // Assuming '4' is the status code for delivered orders

$result1 = $conn->query($deliv_sql);

// Initialize $total_deliv to 0
$total_deliv = 0;

if ($result1->num_rows > 0) {
    $row = $result1->fetch_assoc();
    $total_deliv = $row['total_deliv'];  // Store the total delivered count
} else {
    $total_deliv = "No data available";  // Fallback if no data
}

$cancl_sql = "SELECT COUNT(*) AS total_can 
              FROM order_tbl 
              WHERE MONTH(order_date) = MONTH(CURDATE()) 
                AND YEAR(order_date) = YEAR(CURDATE()) 
                AND status_code = '5'";  // Assuming '5' is the status code for delivered orders

$result = $conn->query($cancl_sql);

// Initialize $total_deliv to 0
$total_can = 0;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_can = $row['total_can'];  // Store the total delivered count
} else {
    $total_can = "No data available";  // Fallback if no data
}




?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />



    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.bootstrap5.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/report_ordrManager.css">


    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.js">

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.2/css/buttons.bootstrap5.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

    <link rel="icon" href="../img/logo.ico" type="image/x-icon">

    <style>
        .active5 {
            background: linear-gradient(180deg, #ff83259b, #a72828);
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


        <div class="container-fluid">
            <h2 class="my-4">Reports</h2>
            <div class="cards">
                <div class="card text-bg-primary mb-3" style="max-width: 20rem;">
                    <div class="card-header">
                        <h5>Daily Sales</h5>
                    </div>
                    <div class="card-body" id="card_con">
                        <i class="fa-solid fa-wallet"></i>
                        <h2 class="card-text">₱<?php echo number_format($daily_sales); ?></h2>
                    </div>
                </div>

                <div class="card text-bg-success mb-3" style="max-width: 20rem;">
                    <div class="card-header">
                        <h5>Monthly Delivered</h5>
                    </div>
                    <div class="card-body" id="card_con">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                        <h2 class="card-text"><?php echo number_format($total_deliv); ?></h2>
                    </div>
                </div>
                <div class="card text-bg-danger mb-3" style="max-width: 20rem;">
                    <div class="card-header">
                        <h5>Monthly Canceled</h5>
                    </div>
                    <div class="card-body" id="card_con">
                        <i class="fa-solid fa-circle-xmark"></i>
                        <h2 class="card-text"><?php echo number_format($total_can); ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="container-fluid">
            <div class="cust_prod">
                <div class="table-responsive overflow-auto">
                    <h2 class="my-4">Top Customers</h2>

                    <table class="table table-bordered table-striped" id="customersTable">
                        <thead>
                            <tr>
                                <th style="background-color: #a72828; color: white;">No.</th>
                                <th style="background-color: #a72828; color: white;">Customer Name</th>
                                <th style="background-color: #a72828; color: white;">Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            $cust3 = "SELECT order_fullname, SUM(order_total) AS total_spent 
                                FROM order_tbl 
                                WHERE status_code != 5
                                GROUP BY order_fullname
                                ORDER BY total_spent DESC 
                                LIMIT 3";

                            $result = $conn->query($cust3);

                            $rank = 1;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                            <td>" . $rank . "</td>
                            <td>" . $row['order_fullname'] . "</td>
                            <td><strong>₱" . $row['total_spent'] . "</strong></td>
                     </tr>";
                                $rank++;
                            }

                            ?>

                        </tbody>
                    </table>
                </div>
                <div class="table-responsive overflow-auto">
                    <h2 class="my-4">Top Products</h2>

                    <table class="table table-bordered table-striped" id="prodsTable">
                        <thead>
                            <tr>
                                <th style="background-color: #a72828; color: white;">No.</th>
                                <th style="background-color: #a72828; color: white;">Product Name</th>
                                <th style="background-color: #a72828; color: white;">Purchased</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            $prod3 = "SELECT p.prod_name, SUM(o.order_qty) AS total_purchased 
                            FROM order_tbl o
                            JOIN product_tbl p ON o.prod_code = p.prod_code
                            WHERE YEAR(o.order_date) = YEAR(CURDATE())
                            AND o.status_code = '4'
                            GROUP BY p.prod_name
                            ORDER BY total_purchased DESC
                            LIMIT 3";

                            $result = $conn->query($prod3);

                            $rank = 1;
                            while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                            <td>" . $rank . "</td>
                            <td>" . $row["prod_name"] . "</td>
                            <td><strong>" . $row["total_purchased"] . "</strong></td>
                            </tr>";
                            $rank++;
                            }



                            ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <br>
        <hr>

        <div class="container-fluid">
            <div class="header-container">
                <h2 class="my-4">Stocks</h2>
                <!-- Button to switch between chart and table -->
                <button id="toggleButton" onclick="toggleView()"><i class="fa-solid fa-repeat"></i> Chart View</button>
            </div>
            <!-- Table container initially shown -->
            <div id="tableContainer" style="display: block;">
                <div class="table-responsive overflow-auto" style="width:100%">
                    <table class="table table-striped" id="stocksTbl">
                        <thead>
                            <tr>
                                <th style="background-color: #a72828; color: white;">Product Name</th>
                                <th style="background-color: #a72828; color: white;">Product Price(₱)</th>
                                <th style="background-color: #a72828; color: white;">Quantity on Hand</th>
                            </tr>
                        </thead>
                        <tbody id="productTableBody">
                            <!-- Table rows will be inserted dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>


            <!-- Container for the chart (initially hidden) -->
            <div id="con-chart" style="display: none;">
                <div id="chart">
                    <div id="chartContainer" style="width:95%;">
                        <canvas id="quantityChart"></canvas>
                    </div>
                </div>
            </div>
            <br>
            <hr>



            <div class="container-fluid">
                <h2 class="my-4">Transactions</h2>


                <!-- Date Picker with icon on the right side -->
                <div class="container mb-1" style="margin-top: 20px;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="input-group date float-end" style="width: 250px;">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-calendar-alt" style="color: #a72828;"></i>
                                </span>
                                <input type="text" id="DatePicker" class="form-control datepicker" placeholder="Select Date" >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Filter Dropdown -->
                <div class="container mb-1" style="margin-top: 0px;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="input-group float-end" style="width: 250px;">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-filter" style="color: #a72828;"></i>
                                </span>
                                <select id="statusFilter" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Success">Success</option>
                                    <option value="Failed">Failed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="tableTransacs">
                    <div class="table-responsive overflow-auto" style="width:100%">
                        <table class="table table-bordered table-striped" id="trnTbl">
                            <thead>
                                <tr>
                                    <th style="background-color: #a72828; color: white;">Transactions ID</th>
                                    <th style="background-color: #a72828; color: white;">Product</th>
                                    <th style="background-color: #a72828; color: white;">Customer</th>
                                    <th style="background-color: #a72828; color: white;">Address </th>
                                    <th style="background-color: #a72828; color: white;">Quantity</th>
                                    <th style="background-color: #a72828; color: white;">Total Payment</th>
                                    <th style="background-color: #a72828; color: white;">Payment Method</th>
                                    <th style="background-color: #a72828; color: white;">Shipper name</th>
                                    <th style="background-color: #a72828; color: white;">Date</th>
                                    <th style="background-color: #a72828; color: white;">Status</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Assume you have established a database connection already
                                $Transql = "
                                        SELECT 
                                            d.transact_code AS `Transaction Code`, 
                                            p.prod_name AS `Product Name`, 
                                            p.prod_img AS `Product Image`, 
                                            o.order_fullname AS `Customer Name`, 
                                            CONCAT(o.order_purok, ', ', o.order_barangay, ', ', o.order_province) AS `Address`, 
                                            o.order_qty AS `Quantity`, 
                                           o.order_total + 
                                                br.Brgy_df / COUNT(*) OVER (PARTITION BY o.cust_id, o.order_date, o.order_barangay) 
                                                AS `Total Payment`,
                                             CONCAT(e.emp_fname, ', ', e.emp_lname) AS `Shipper Name`,
                                             o.order_mop AS 'Payment Method', 
                                            d.transact_date AS 'Date', 
                                            d.transact_status AS 'Status',
                                            o.cust_id AS 'Customer ID'
                                        FROM 
                                            delivery_transactbl d
                                        JOIN 
                                            order_tbl o ON d.order_id = o.order_id
                                        JOIN 
                                            product_tbl p ON o.prod_code = p.prod_code
                                        JOIN 
                                            emp_tbl e ON d.shipper_id = e.emp_id
                                        JOIN 
                                            brgy_tbl br ON o.order_barangay = br.Brgy_Name
                                        ORDER BY 
                                            o.cust_id ASC, d.transact_date ASC;
                                        ";

                                $result = $conn->query($Transql);
                                $previousCustId = null;
                                $previousOrderDate = null;
                                $highlightClass = '';

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        if ($row['Customer ID'] !== $previousCustId || $row['Date'] !== $previousOrderDate) {
                                            $highlightClass = 'group-highlight';
                                        } else {
                                            $highlightClass = '';
                                        }

                                        $badgeClass = '';
                                        // Assuming status_name is not part of your SQL, so you might need to adjust accordingly
                                        switch ($row['Status']) {
                                            case 'Ongoing':
                                                $badgeClass = 'bg-info text-white';
                                                break;
                                            case 'Success':
                                                $badgeClass = 'bg-success text-white';
                                                break;
                                            case 'Failed':
                                                $badgeClass = 'bg-danger text-white';
                                                break;
                                            default:
                                                $badgeClass = 'bg-light text-dark';
                                        }

                                        echo "<tr class='{$highlightClass}'>
                                            <td>{$row['Transaction Code']}</td>
                                            <td class='text-center'>
                                                <div class='d-flex align-items-center'>
                                                    <img src='../{$row['Product Image']}' alt='{$row['Product Name']}' class='product-img' />
                                                    <span class='ms-2'>{$row['Product Name']}</span>
                                                </div>
                                            </td>
                                            <td>{$row['Customer Name']}</td>
                                            <td>{$row['Address']}</td>
                                            <td>{$row['Quantity']}</td>
                                            <td>" . number_format($row['Total Payment'], 2) . "</td>
                                             <td>{$row['Payment Method']}</td>
                                            <td>{$row['Shipper Name']}</td>
                                            <td>{$row['Date']}</td>
                                            <td class='text-center'><span class='badge {$badgeClass}'>{$row['Status']}</span></td>
                                            
                                          </tr>";

                                        $previousCustId = $row['Customer ID'];
                                        $previousOrderDate = $row['Date'];
                                    }
                                } else {
                                    echo "<tr><td colspan='8'>No Transactions found</td></tr>";
                                }

                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- DataTables JS -->
            <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.datatables.net/2.1.6/js/dataTables.js"></script>
            <script src="https://cdn.datatables.net/2.1.6/js/dataTables.bootstrap5.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.datatables.net/buttons/3.1.2/js/dataTables.buttons.js"></script>
            <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.bootstrap5.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
            <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.html5.min.js"></script>
            <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.print.min.js"></script>
            <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.colVis.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

            <!-- Include DataTables JS -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <!-- Include DataTables JS -->

            <script>
                function updateClock() {
                    const now = new Date();

                    // Format hours, minutes, and seconds
                    let hours = now.getHours();
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const seconds = String(now.getSeconds()).padStart(2, '0');

                    // Determine AM/PM
                    const ampm = hours >= 12 ? 'PM' : 'AM';

                    // Convert hours from 24-hour to 12-hour format
                    hours = hours % 12;
                    hours = hours ? hours : 12; // the hour '0' should be '12'

                    // Format time
                    const timeString = `${String(hours).padStart(2, '0')}:${minutes}:${seconds} ${ampm}`;

                    // Update the clock and date elements
                    document.getElementById('clock').textContent = timeString;

                    // Format the date
                    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    const day = days[now.getDay()];
                    const date = now.toLocaleDateString('en-US', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });
                    document.getElementById('date').textContent = `${day}, ${date}`;
                }

                setInterval(updateClock, 1000); // Update the clock every second
                updateClock(); // Initial call to display the time immediately
            </script>


            <script>
                // Prepare data from PHP
                const productNames = <?php echo json_encode($products); ?>; // Product names
                const productQuantities = <?php echo json_encode($quantities); ?>; // Quantities on hand
                const productPrices = <?php echo json_encode($prices); ?>; // Product prices

                // Function to dynamically adjust the chart height based on number of products
                function adjustChartHeight() {
                    const chartContainer = document.getElementById('chartContainer');
                    const productCount = productNames.length;

                    // Adjust height dynamically based on the number of products (example: 50px per product)
                    chartContainer.style.height = (productCount * 50) + 'px';
                }

                // Call the function to adjust the chart height initially
                adjustChartHeight();

                // Create the chart
                const ctx = document.getElementById('quantityChart').getContext('2d');

                // Creating a gradient background for the bars
                const gradient = ctx.createLinearGradient(0, 0, 400, 0); // Adjust gradient for horizontal bars
                gradient.addColorStop(0, '#ff8225');
                gradient.addColorStop(1, '#ffbb66');

                const quantityChart = new Chart(ctx, {
                    type: 'bar', // Bar chart
                    data: {
                        labels: productNames, // Product names as labels
                        datasets: [{
                            label: '  Quantity on Hand',
                            data: productQuantities, // Quantities for each product
                            backgroundColor: gradient, // Gradient color for the bars
                            borderColor: '#ff6347', // Bar border color
                            borderWidth: 2,
                            borderRadius: 8, // Set bar border radius
                            borderSkipped: false, // Ensure radius applies to all corners

                            // Hover settings
                            hoverBackgroundColor: '#ff4500', // Change bar color on hover
                            hoverBorderColor: '#ff0000', // Change border color on hover
                            hoverBorderWidth: 4, // Increase border width on hover
                            hoverBorderRadius: 15, // Increase border radius on hover
                            hoverOffset: 6 // Make the bars "grow" on hover
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false, // Ensure chart scales properly when new products are added
                        indexAxis: 'y', // Make the bars horizontal
                        plugins: {
                            title: {
                                display: true,
                                text: 'Quantity on Hand of Products',
                                font: {
                                    size: 30,
                                    weight: 'bold'
                                },
                                padding: {
                                    top: 10,
                                    bottom: 30
                                }
                            },
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: true, // Enable tooltips
                                bodyFont: {
                                    size: 16, // Set font size for tooltip text
                                    color: 'black'
                                },
                                titleFont: {
                                    size: 18, // Set font size for tooltip title
                                    weight: 'bold'
                                },
                                titleColor: 'black',
                                bodyColor: 'black',
                                padding: 15, // Add padding to the tooltip
                                backgroundColor: '#fff', // Change background color of the tooltip
                                borderColor: 'black', // Set border color for the tooltip
                                borderWidth: 1 // Set border width for the tooltip
                            }
                        },
                        scales: {
                            x: { // Now the x-axis represents the quantities
                                beginAtZero: true,
                                ticks: {
                                    font: {
                                        size: 15
                                    },
                                    color: 'black'
                                },
                                grid: {
                                    display: false
                                }
                            },
                            y: { // Now the y-axis represents the product names
                                ticks: {
                                    font: {
                                        size: 20
                                    },
                                    color: '#333'
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeInOutBounce'
                        },
                        layout: {
                            padding: {
                                left: 10,
                                right: 10,
                                top: 10,
                                bottom: 10
                            }
                        }
                    }
                });

                // Function to update chart data when new products are added
                function updateChart(newProductNames, newQuantities) {
                    // Update the data arrays
                    quantityChart.data.labels = newProductNames;
                    quantityChart.data.datasets[0].data = newQuantities;

                    // Adjust chart height dynamically
                    adjustChartHeight();

                    // Update the chart
                    quantityChart.update();
                }

                // Function to populate the table with product data
                function populateTable() {
                    const tableBody = document.getElementById('productTableBody');
                    tableBody.innerHTML = ''; // Clear existing table rows

                    productNames.forEach((productName, index) => {
                        const quantity = Math.floor(productQuantities[index]); // Remove decimal part
                        const row = `<tr>
                        <td>${productName}</td>
                        <td>₱ ${productPrices[index]}</td>
                        <td>${quantity}</td> <!-- Display QOH without decimals -->
                    </tr>`;
                        tableBody.innerHTML += row;
                    });
                }

                // Function to toggle between chart and table view
                function toggleView() {
                    const tableContainer = document.getElementById('tableContainer');
                    const chart = document.getElementById('con-chart'); // Get the chart div
                    const button = document.getElementById('toggleButton');

                    if (chart.style.display === 'none') {
                        // Show the chart and hide the table

                        chart.style.display = 'block';
                        tableContainer.style.display = 'none';
                        button.innerHTML = '<i class="fa-solid fa-repeat"></i> Table View';
                    } else {
                        // Hide the chart and show the table
                        populateTable();
                        chart.style.display = 'none';
                        tableContainer.style.display = 'block';
                        button.innerHTML = '<i class="fa-solid fa-repeat"></i> Chart View';

                        // Populate the table with product data

                    }
                }


                $(document).ready(function() {
                    // First, populate the table
                    populateTable();

                    // Initialize DataTables after populating the table
                    var table = $('#stocksTbl').DataTable({
                        "pageLength": 10,
                        "ordering": true,
                        "autoWidth": true,
                        "responsive": true,
                        "order": [
                            [2, 'desc'] // Sorting by quantity on hand (descending)
                        ],
                        "dom": 'Bfrtip', // Include buttons in DOM
                        "buttons": [{
                                extend: 'copy',
                                text: '<i class="fas fa-copy"></i> Copy',
                                titleAttr: 'Copy'
                            },
                            {
                                extend: 'excelHtml5', // Use excelHtml5 for Excel export
                                text: '<i class="fas fa-file-excel"></i> Excel',
                                titleAttr: 'Excel',
                                exportOptions: {
                                    columns: ':visible' // Export only visible columns
                                }
                            },
                            {
                                extend: 'pdfHtml5', // Use pdfHtml5 for PDF export
                                text: '<i class="fas fa-file-pdf"></i> PDF',
                                titleAttr: 'PDF',
                                orientation: 'portrait', // Set PDF orientation
                                pageSize: 'A4',
                                exportOptions: {
                                    columns: ':visible' // Export only visible columns
                                }
                            }

                        ]
                    });
                });
            </script>


            <script>
                $(document).ready(function() {
                    // Initialize DataTables with pagination, and set page length to 10
                    var table = $('#trnTbl').DataTable({
                        "pageLength": 10,
                        "ordering": true,
                        "autoWidth": true,
                        "responsive": true,
                        "order": [
                            [6, 'desc']
                        ],
                        dom: 'Bfrtip', // Ensure buttons are displayed
                        buttons: [{
                                extend: 'copy',
                                text: '<i class="fas fa-copy"></i> Copy',
                                titleAttr: 'Copy'
                            },
                            {
                                extend: 'excel',
                                text: '<i class="fas fa-file-excel"></i> Excel',
                                titleAttr: 'Excel'
                            },
                            {
                                extend: 'pdf',
                                text: '<i class="fas fa-file-pdf"></i> PDF',
                                titleAttr: 'PDF'
                            },
                            {
                                extend: 'colvis',
                                text: '<i class="fas fa-columns"></i> Columns',
                                titleAttr: 'Column Visibility'
                            }
                        ]
                    });

                    // Initialize Date Picker
                    $('#DatePicker').datepicker({
                        format: 'yyyy-mm-dd',
                        autoclose: true,
                        todayHighlight: true
                    });

                    // Filter DataTable on Date Picker change
                    $('#DatePicker').on('changeDate', function() {
                        var selectedDate = $(this).val();
                        table.columns(8).search(selectedDate).draw(); // 6 is the index for the 'Date' column
                    });

                    // Filter DataTable on Status Dropdown change
                    $('#statusFilter').on('change', function() {
                        var selectedStatus = $(this).val();
                        table.columns(9).search(selectedStatus).draw(); // 7 is the index for the 'Status' column
                    });
                });
            </script>

<?php 
 $conn->close();
?>



</body>

</html>