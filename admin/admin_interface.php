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
            case 'Order Manager':
                header("Location: ../ordr_manager/order_manager.php");
                exit;
            case 'Cashier':
                header("Location: ../cashier/cashier.php");
                exit;
                break;
            default:
        }
    }
} else {
    header("Location: ../login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Interface</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <!-- Custom CSS -->
    <style>
        body {
            overflow-x: hidden;
            background-color: #f8f9fa;
        }


        #sidebar-wrapper .sidebar-heading .sidebar-title {
            font-size: 1.5rem;
            display: inline;
        }

        #wrapper {
            display: flex;
            width: 100%;
            height: 100%;
            /* Full viewport height */
        }

        #sidebar-wrapper {
            min-height: 100%;
            width: 80px;
            /* Default width for icons only */
            background-color: #a72828;
            color: #fff;
            transition: width 0.3s ease;
            overflow-y: auto;
            /* Allow vertical scrolling */
            position: relative;
            overflow-x: hidden;
            /* Prevent horizontal scrolling */
            border-right: 1px solid #ddd;
            /* Light border to separate from content */
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            /* Subtle shadow */
        }

        #sidebar-wrapper.expanded {
            width: 250px;
            /* Expanded width */
        }

        #sidebar-wrapper .sidebar-heading {
            padding: 1rem;
            display: flex;
            align-items: center;
            background-color: #FF8225;
            color: #fff;
            border-bottom: 1px solid #ddd;
            /* Border for separation */
        }

        #sidebar-wrapper .logo-img {
            width: 40px;
            /* Adjust size as needed */
            height: 40px;
            margin-right: 10px;
            /* Space between logo and text */
        }

        #sidebar-wrapper .sidebar-title {
            font-size: 1.5rem;
            display: inline;
            /* Ensure title is always visible */
        }

        #sidebar-wrapper .list-group {
            width: 100%;
        }

        #sidebar-wrapper .list-group-item {
            background-color: #a72828;
            color: #fff;
            border: none;
            padding: 1rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            border-radius: 0;
            /* Remove default border radius */
            transition: background-color 0.2s ease;
            /* Smooth hover effect */
        }

        #sidebar-wrapper .list-group-item i {
            font-size: 1.5rem;
            margin-right: 15px;
        }

        #sidebar-wrapper .list-group-item span {
            display: none;
            /* Hide text in default state */
            margin-left: 10px;
            white-space: nowrap;
            /* Prevent text wrapping */
        }

        #sidebar-wrapper.expanded .list-group-item span {
            display: inline;
            /* Show text in expanded state */
        }

        #sidebar-wrapper .list-group-item:hover {
            background-color: #8c1c1c;
            /* Darker color on hover */
        }

        #sidebar-wrapper .toggle-btn {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #FF8225;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
            /* Button shadow */
        }

        #sidebar-wrapper .toggle-btn:hover {
            background-color: #a72828;
        }

        #page-content-wrapper {
            flex: 1;
            padding: 20px;
            transition: margin-left 0.3s ease;
            background-color: #f8f9fa;
            /* Slightly different background */
        }

        #page-content-wrapper.sidebar-expanded {
            margin-left: 0px;
            /* Match the expanded sidebar width */
        }

        .navbar-light {
            background-color: #FF8225;
        }

        .navbar-light .navbar-nav .nav-link {
            color: black;


        }

        .navbar-light .navbar-nav .nav-link:hover {
            color: #a72828;
        }


        /* Hide sidebar heading text when collapsed */
        #sidebar-wrapper:not(.expanded) .sidebar-title {
            display: none;
        }

        #sidebar-wrapper:not(.expanded) .logo-img {
            width: 30px;
            /* Adjust size when collapsed */
            height: 30px;
        }

        /*.container-fluid-header{
    display: flex;
    background-color: #FF8225;
}*/
        .container-box-wrapper {
            display: flex;
            justify-content: space-between;
            /* Space boxes evenly */
            gap: 20px;
            /* Space between boxes */
            margin: 20px;
            /* Optional margin around the wrapper */
        }

        .container-box {
            display: flex;
            align-items: center;
            /* Align items vertically center */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            width: 35%;
            /* Adjust width as needed */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* Optional shadow for better look */
        }

        .container-box i {
            margin-right: 10px;
            /* Space between icon and text */
            font-size: 40px;
            /* Adjust icon size as needed */
            color: #8c1c1c;
            /* Adjust icon color as needed */
        }

        .container-box div {
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            /* Center text horizontally */
            flex: 1;
            /* Allows div to take up remaining space */
        }

        .sales-amount {
            font-size: 40px;
            color: #a72828;
            font-weight: bold;
            margin: 0;
        }
        .prod-name {
            font-size: 35px;
            color: #a72828;
            font-weight: bold;
            margin: 0;
        }
        .stars-total{
            font-size: 23px;
            color: #FF8225;
            font-weight: bold;
            margin: 0;
        }

        .sales-label {
            display: block;
            font-size: 20px;
            color: #FF8225;
            font-weight: 600;
            margin: 0;
        }

        .rating_scale-container {
            display: flex;
        }

        .container-rate {
            display: flex;
            background-color: white;
            border: 2px solid #a72828;
            width: 50%;
            height: 60vh;

            color: #a72828;
            margin-top: 20px;
            margin-left: 50px;
            border-radius: 10px;
            justify-content: center;
            text-align: center;
            align-items: center;
            margin-bottom: 30px;

        }

        .scale-nav {
            display: flex;
            flex-direction: column;
            color: green;

            width: 40%;
            margin-left: 60px;
            margin-top: 40px;
            border-radius: 10px;
            height: 100%;
            align-content: center;
            text-align: center;
            justify-content: center;

        }

        .scale-nav span {
            color: #8c1c1c;
            font-size: 50px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        #chartContainer {
            height: 80%;
            width: 100%;
        }

        canvas {
            width: 85% !important;
        }

        table {
            width: 100%;
            margin: 0 auto;
            border-collapse: collapse;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            border-radius: 30px;
            border-radius: 10px;
        }

        th,
        td {
            border: 1px solid #dee2e6;
            padding: 12px 15px;
            text-align: left;
            transition: background-color 0.3s;

        }

        th {
            background-color: #8c1c1c;
            color: #fff;
            text-transform: uppercase;
            font-size: 20px;
            font-weight: bold;
            text-align: center;

        }

        td {
            background-color: #f9f9f9;
            text-align: center;
            font-size: 17px;
        }

        tr:nth-child(even) td {
            background-color: #f2f2f2;
        }

        tr:hover td {
            background-color: #e2e6ea;
        }

        .table-container {
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
</head>

<body>

    <div class="d-flex" id="wrapper">
        <?php
        include '../includes/sidebar.php';
        ?>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <?php
            include '../includes/admin-navbar.php';
            ?>

            <div class="container-fluid">
                <div class="content-header">
                    <h1 class="mt-4" id="dashboard">Dashboard</h1>
                </div>
                <?php

                $sql = "SELECT SUM(order_total) AS monthly_sales 
        FROM order_tbl 
        WHERE MONTH(order_date) = MONTH(CURDATE()) 
          AND YEAR(order_date) = YEAR(CURDATE())
          AND status_code = '4'";
                $result = $conn->query($sql);

                $monthly_sales = 0;

                if ($result->num_rows > 0) {

                    while ($row = $result->fetch_assoc()) {
                        $monthly_sales = $row['monthly_sales'];
                    }
                } else {
                    $monthly_sales = "No data available";
                }
                ?>
                <?php
                $sql = "SELECT COUNT(cust_id) AS total_customers FROM customers";
                $result = $conn->query($sql);

                $total_customers = 0;

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $total_customers = $row['total_customers'];
                    }
                } else {
                    $total_customers = "No data available";
                }


                ?>
                <?php
                $sql = "SELECT COUNT(emp_id) AS total_employee FROM emp_tbl";
                $result = $conn->query($sql);

                $total_employee = 0;

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $total_employee = $row['total_employee'];
                    }
                } else {
                    $total_employee = "No data available";
                }
                ?>

                <?php
                // Modified query to sum the stars for each product
                $sql = "SELECT r.prod_code, pt.prod_name, SUM(r.rev_star) AS total_stars
                FROM ratings_tbl r
                JOIN product_tbl pt ON r.prod_code = pt.prod_code
                GROUP BY r.prod_code
                ORDER BY total_stars DESC 
                LIMIT 1";

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // Output the product with the highest total stars
                    while ($row = $result->fetch_assoc()) {
                        $highest_reviews = $row["total_stars"];
                        $prod_name = $row["prod_name"];
                    }
                } else {
                    $highest_reviews = "No reviews found";
                }
                ?>


                <div class="container-box-wrapper">
                    <div class="container-box">
                        <i class="fas fa-money-bill-wave"></i>
                        <div>
                            <a href="viewSales.php" style="text-decoration: none; color: inherit;">
                                <span class="sales-amount">₱ <?php echo number_format($monthly_sales); ?></span>
                                <span class="sales-label">Monthly Sales (<?php echo date('F'); ?>) </span>
                            </a>
                        </div>
                    </div>
                    <div class="container-box">
                        <i class="fas fa-users"></i>
                        <div>
                            <span class="sales-amount"><?php echo number_format($total_customers); ?></span>
                            <span class="sales-label">Customer</span>
                        </div>
                    </div>

                    <div class="container-box">
                        <a href="addEmployee.php" style="text-decoration: none; color: inherit;">
                            <i class="fas fa-briefcase"></i>
                        </a>
                        <div>
                            <a href="addEmployee.php" style="text-decoration: none; color: inherit;">
                                <span class="sales-amount"><?php echo number_format($total_employee); ?></span>
                                <span class="sales-label">Employee</span>
                            </a>
                        </div>
                    </div>

                    <div class="container-box">
                        <i class="fa-solid fa-star"></i><span class="stars-total"><?php echo ($highest_reviews); ?></span>
                        <div>
                            <p class="prod-name"><?php echo ($prod_name); ?></p>
                            <p class="sales-label">Highest Rated Product</p>
                        </div>
                    </div>
                </div>
                <br>
                <hr>
                <?php
                // Assuming you have an existing database connection in $conn
                $catPurchased = "SELECT 
                    c.category_name, 
                    SUM(o.order_qty) AS total_purchased,
                    (SUM(o.order_qty) / (SELECT SUM(order_qty) FROM order_tbl o2
                                         WHERE YEAR(o2.order_date) = YEAR(CURDATE())
                                         AND o2.status_code = '4')) * 100 AS purchase_percentage
                FROM 
                    order_tbl o
                JOIN 
                    product_tbl p ON o.prod_code = p.prod_code
                JOIN 
                    category_tbl c ON p.category_code = c.category_code
                WHERE 
                    YEAR(o.order_date) = YEAR(CURDATE())
                    AND o.status_code = '4'
                GROUP BY 
                    c.category_name
                ORDER BY 
                    purchase_percentage DESC;";

                // Execute the query
                $result = $conn->query($catPurchased);

                // Check if the query was successful
                if ($result) {
                    $dataPoints = [];

                    // Fetch the rows and prepare the data for the chart
                    while ($row = $result->fetch_assoc()) {
                        $dataPoints[] = [
                            "label" => $row['category_name'],
                            "y" => round($row['purchase_percentage'])
                        ];
                    }

                    $result->free();


                    // Convert to JSON if needed for JavaScript
                    $jsonDataPoints = json_encode($dataPoints);
                } else {
                    // Handle error if query failed
                    echo "Error: " . $conn->error;
                }


                ?>


                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <div class="rating_scale-container">
                    <div class="container-rate">
                        <canvas id="chartContainer"></canvas>
                    </div>

                    <script>
                        // Prepare data from PHP
                        const dataPoints = <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>;

                        // Create the chart
                        const ctx = document.getElementById('chartContainer').getContext('2d');

                        const chartContainer = new Chart(ctx, {
                            type: 'pie', // Change to pie chart
                            data: {
                                labels: dataPoints.map(point => point.label), // Category names as labels
                                datasets: [{
                                    data: dataPoints.map(point => point.y), // Purchase percentages
                                    backgroundColor: [
                                        '#eb0a1e', '#011ca3', '#f06b03', '#2bd2ff', '#000ebd'
                                    ], // Example colors for each category
                                    borderColor: '#ffffff',
                                    borderWidth: 1,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false, // Ensure chart scales properly
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Product Purchases by Category for Current Year (in %)',
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
                                        display: true,
                                        position: 'right', // Display legend on the right side
                                        labels: {
                                            font: {
                                                size: 20
                                            }
                                        }
                                    },
                                    tooltip: {
                                        enabled: true,
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
                                        borderWidth: 1, // Set border width for the tooltip
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                return tooltipItem.label + ': ' + tooltipItem.raw + '%';
                                            }
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
                    </script>


                    <?php
                    $sql = "SELECT p.prod_name, SUM(o.order_qty) AS total_purchased 
                    FROM order_tbl o
                    JOIN product_tbl p ON o.prod_code = p.prod_code
                    WHERE YEAR(o.order_date) = YEAR(CURDATE())
                    AND o.status_code = '4'
                    GROUP BY p.prod_name
                    ORDER BY total_purchased DESC
                    LIMIT 5";
                    $result = $conn->query($sql);

                    $topProducts = [];
                    $rank = 1;

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $topProducts[] = array(
                                "rank" => $rank,
                                "prod_name" => $row['prod_name'],
                                "order_qty" => number_format($row['total_purchased'], 2)
                            );
                            $rank++;
                        }
                    } else {
                        $topProducts = [];
                    }


                    ?>
                    <div class="scale-nav">
                        <span>Top Products</span>

                        <table>
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Product Name</th>
                                    <th>Quantity Purchased</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProducts as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['rank']); ?></td>
                                        <td><?php echo htmlspecialchars($product['prod_name']); ?></td>
                                        <td><?php echo number_format($product['order_qty'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /#page-content-wrapper -->
        </div>
        <!-- /#wrapper -->

        <!-- Bootstrap and JavaScript -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

        <script>
            // Toggle sidebar
            $("#menu-toggle, #menu-toggle-top").click(function(e) {
                e.preventDefault();
                $("#sidebar-wrapper").toggleClass("expanded");
                $("#page-content-wrapper").toggleClass("sidebar-expanded");
                // Change icon on toggle
                let icon = $("#sidebar-wrapper .toggle-btn i");
                if ($("#sidebar-wrapper").hasClass("expanded")) {
                    icon.removeClass("fa-chevron-right").addClass("fa-chevron-left");
                } else {
                    icon.removeClass("fa-chevron-left").addClass("fa-chevron-right");
                }
            });
        </script>

</body>

</html>