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
        <?php
 $sql = "SELECT SUM(total) AS total_sales
         FROM (SELECT SUM(order_qty * order_total) AS total
                FROM order_tbl 
                WHERE status_code = '4'
                UNION ALL
                SELECT SUM(o.pos_qty * p.prod_price) AS total
                FROM pos_tbl o 
                JOIN product_tbl p on p.prod_code = o.prod_code) 
                AS combined_sales";

        $result = $conn->query($sql);

        $total_sales = 0;

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $total_sales = $row['total_sales'];
            }
        } else {
            $total_sales = "No data available";
        }
                ?>
                <?php

        $sql = "SELECT SUM(order_qty * order_total) AS online_sales 
            FROM order_tbl 
            WHERE status_code = '4'";
                $result = $conn->query($sql);

                $online_sales = 0;

                if ($result->num_rows > 0) {

                    while ($row = $result->fetch_assoc()) {
                        $online_sales = $row['online_sales'];
                    }
                } else {
                    $online_sales = "No data available";
                }
                ?>
               <?php

$sql = "SELECT SUM(total) as total_purchased 
            FROM (
            SELECT COUNT(*) as total
                    FROM order_tbl
                    UNION ALL 
                    SELECT COUNT(*) as total
                    FROM pos_tbl )
                    AS combined_sales" ;

$result = $conn->query($sql);

$total_orders = 0;

if ($result) {
    $row = $result->fetch_assoc();
    $total_orders = $row['total_purchased'];
} else {
    echo "Error: " . $conn->error;
}
?>
 <?php

        $sql = "SELECT SUM(order_qty * order_total) AS daily_sales 
            FROM order_tbl 
            WHERE status_code = '4'
            AND DATE(order_date) = CURDATE()";
                $result = $conn->query($sql);

                $daily_sales = 0;

                if ($result->num_rows > 0) {

                    while ($row = $result->fetch_assoc()) {
                        $daily_sales = $row['daily_sales'];
                    }
                } else {
                    $daily_sales = "No data available";
                }
                ?>
                 <?php

        $sql = "SELECT SUM(o.pos_qty * p.prod_price) AS pos_sales 
            FROM pos_tbl o
            JOIN product_tbl p on p.prod_code = o.prod_code";
                $result = $conn->query($sql);

                $daily_sales = 0;

                if ($result->num_rows > 0) {

                    while ($row = $result->fetch_assoc()) {
                        $pos_sales = $row['pos_sales'];
                    }
                } else {
                    $pos_sales = "No data available";
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

       


        canvas {
            width: 95% !important;
            height: 400px;
        }
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
            width: 34%;

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
        }

        .sales-label {
            display: block;
            font-size: 20px;
            color: #FF8225;
            font-weight: 600;
        }
           
           
            /* Optional margin around the wrapper */

        
       .chart-row{
           display: flex;
            justify-content: space-between;
            align-items: center;
            /* Align items vertically center */
            padding: 10px;
           gap: 20px;
            border-radius: 5px;
        
            

            /* Adjust width as needed */
           
            /* Optional shadow for better look */
            
            
                 
       }
         .dashboard-card-charts {
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
             border: 1px solid #ddd;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            width: 100%;
            height: 50%;
            margin-left: 10px;
            
        }
       .chart-row h6{
         font-weight: bold;
            font-size: 25px;
            color: #a72828;
            font-weight: bold;
       }
      .most-ordered{
    display: flex;
    width: 100%;
    height: 65vh; 
    margin-top: 10px;
    margin-left: 5%;
    padding-top: 20px;
    border-radius: 10px;
    background-color: #fff;
    flex-direction: column;
    text-align: center;
}

.most-ordered {
    display: flex;
    width: 90%;
    height: 65vh; 
    margin-top: 10px;
    margin-left: 5%;
    padding: 20px;
    border-radius: 10px;
    background-color: #f8f9fa;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    flex-direction: column;
    text-align: center;
}

.most-ordered-table {
    flex-grow: 1;
    overflow-y: auto; /* Enable vertical scrolling */
    max-height: calc(65vh - 70px); /* Adjust height minus header */
}


.custom-table {
    width: 100%;
    margin: 0 auto;
    border-collapse: separate;
    border-spacing: 0;
    background-color: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    text-align: center;
}

.custom-table th, 
.custom-table td {
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
}

.custom-table thead th {
    background-color: #343a40; /* Dark header background */
    color: white;
    font-weight: bold;
    position: sticky; /* Keep the header fixed */
    top: 0;
    z-index: 10; /* Ensure the header is above other content */
}

.table-responsive {
    overflow-y: auto; /* Enable vertical scrolling */
    max-height: 60vh; /* Adjust height to fit your design */
}

.custom-table tbody tr {
    background-color: white; /* Set all rows to white */
    transition: background-color 0.3s;
}

.custom-table tbody tr:hover {
    background-color: #f1f3f5; /* Light background on hover */
}
.table-title{
    color: #a72828;
    font-size: 30px;
    font-weight: 700;
}
.combo-box{
    font-size: 20px;
    color: #a72828;
}
.combo-box input{
    border-radius: 10px;
    border-color: #8c1c1c;
    color: #FF8225;
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
        <div class="content-wrap">
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1 class="h2">Sales Board</h1>
                </div>
                <div class="container-box-wrapper">
                    <div class="container-box">
                        <i class="fas fa-money-bill-wave"></i>
                        <div>
                             <span class="sales-label">Total Sales </span>
                            <span class="sales-amount">₱ <?php echo number_format($total_sales); ?></span>
                           
                        </div>
                    </div>
                    <div class="container-box">
                       <i class="fas fa-box"></i>
                        <div>
                            <span class="sales-label">Total Purchased Products</span>
                            <span class="sales-amount"><?php echo number_format($total_orders); ?></span>
                            
                        </div>
                    </div>
                    
                    <div class="container-box">
                       
                        <i  class="fas fa-shopping-bag"></i>
                        
                        <div>
                           <span class="sales-label">Online Sale</span>
                            <span class="sales-amount">₱ <?php echo number_format($online_sales); ?></span>
                        </div>
                        
                    </div>
                     <div class="container-box">
                       
                       <i class="fas fa-cash-register"></i>
                        
                        <div>
                           <span class="sales-label">POS Sale</span>
                            <span class="sales-amount">₱ <?php echo number_format($pos_sales); ?></span>
                        </div>
                        
                    </div>
                </div>
               
                <hr>
              <?php
// Initialize an array with 12 elements (one for each month)
$monthlyRevenue = array_fill(0, 12, 0);

$catPurchasedMonthly = " SELECT 
        purchase_month,
        SUM(total_revenue) AS total_revenue
    FROM (
        SELECT 
            MONTH(o.order_date) AS purchase_month,
            SUM(o.order_qty * o.order_total) AS total_revenue
        FROM 
            order_tbl o
        WHERE 
            YEAR(o.order_date) = YEAR(CURDATE())
            AND o.status_code = '4'
        GROUP BY 
            purchase_month

        UNION ALL

        SELECT 
            MONTH(o.transac_date) AS purchase_month,  -- Assuming pos_date exists in pos_tbl
            SUM(o.pos_qty * p.prod_price) AS total_revenue
        FROM 
            pos_tbl o
            JOIN product_tbl p on o.prod_code = p.prod_code
        WHERE 
            YEAR(o.transac_date) = YEAR(CURDATE())  -- Assuming pos_date exists in pos_tbl
        GROUP BY 
            purchase_month
    ) AS combined_sales
    GROUP BY 
        purchase_month
";

$result = $conn->query($catPurchasedMonthly);

// Populate the $monthlyRevenue array with the data from the query
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $monthlyRevenue[$row['purchase_month'] - 1] = $row['total_revenue']; // Adjust for 0-indexed array
    }
    $result->free();
}

// Convert PHP array to JSON for JavaScript usage
$jsonRevenueData = json_encode($monthlyRevenue);
?>

<?php
// Initialize an array with 5 elements (or more depending on how many years you want to track)
$yearlyRevenue = array_fill(0, 5, 0); // Adjust the number of years as needed

// Query to get total revenue for each year from both order_tbl and pos_tbl
$catPurchasedYearly = "SELECT 
        purchase_year,
        SUM(total_revenue) AS total_revenue
    FROM (
        SELECT 
            YEAR(o.order_date) AS purchase_year,
            SUM(o.order_qty * o.order_total) AS total_revenue
        FROM 
            order_tbl o
        WHERE 
            o.status_code = '4'
        GROUP BY 
            purchase_year

        UNION ALL

        SELECT 
            YEAR(o.transac_date) AS purchase_year,  -- Assuming pos_date exists in pos_tbl
            SUM(o.pos_qty * p.prod_price) AS total_revenue
        FROM 
            pos_tbl o
        JOIN product_tbl p on o.prod_code = p.prod_code
        GROUP BY 
            purchase_year
    ) AS combined_sales
    GROUP BY 
        purchase_year
    ORDER BY 
        purchase_year"; // Optional: Order by year

$result = $conn->query($catPurchasedYearly);

// Populate the $yearlyRevenue array with the data from the query
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $year_index = date('Y') - $row['purchase_year']; // Calculate index based on the current year
        if ($year_index >= 0 && $year_index < count($yearlyRevenue)) {
            $yearlyRevenue[$year_index] = $row['total_revenue']; // Store the total revenue for the year
        }
    }
    $result->free();
}

// Convert PHP array to JSON for JavaScript usage
$jsonYearlyData = json_encode($yearlyRevenue);
?>


                <!-- Revenue Updates and Yearly Sales -->
                <div class="chart-row">
                    <div class="col-md-6 mb-1">
                        <div class="dashboard-card-charts" style="padding-right: 5px;">
                           <h6>Monthly Sales Updates for <?php echo date('Y'); ?></h6>
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6 mb-1">
                        <div class="dashboard-card-charts" >
                            <h6>Yearly Sales</h6>
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>

                </div>
              <div class="most-ordered">
                <div class="combo-box">
    <h6 class="table-title">Toal Purchase Products</h6>
    <label for="orderDate">Select Order Date:</label>
    <input type="date" id="orderDate" onchange="filterOrdersByDate()">
</div>
    <div class="most-ordered-table">
        <table class="custom-table">
            <thead class="table-dark">
                <tr>
                    <th>Product Name</th>
                    <th>Total Ordered Price</th>
                </tr>
            </thead>
            <tbody id="tableBody">
               <?php
// SQL query to get product name and total ordered price from both order_tbl and pos_tbl
$query = "SELECT 
        prod_name, 
        SUM(total_ordered_price) AS total_ordered_price
    FROM (
        SELECT 
            p.prod_name, 
            SUM(o.order_qty * o.order_total) AS total_ordered_price
        FROM 
            order_tbl o
        JOIN 
            product_tbl p ON o.prod_code = p.prod_code
        WHERE 
            o.status_code = '4'
        GROUP BY 
            p.prod_name

        UNION ALL

        SELECT 
            p.prod_name, 
            SUM(o.pos_qty * p.prod_price) AS total_ordered_price
        FROM 
            pos_tbl o
        JOIN product_tbl p on o.prod_code = p.prod_code
        GROUP BY 
            p.prod_name
    ) AS combined_sales
    GROUP BY 
        prod_name
    ORDER BY 
        total_ordered_price DESC"; // Sort by highest ordered price

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['prod_name']) . "</td>
                <td>₱ " . number_format($row['total_ordered_price'], 2) . "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='2'>No data available</td></tr>";
}
?>

            </tbody>
        </table>
    </div>
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
        
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
         
        // Revenue Updates Chart
       const revenueData = <?php echo $jsonRevenueData; ?>;

    // Render the chart with the dynamic data
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul','Aug','Sep','Oct','Nov','Dec'],
            datasets: [{
                label: 'Monthly Sale',
                data: revenueData,  // Use the PHP-generated data here
                backgroundColor: '#FF8225',
                 borderRadius: 50
            }]
        }
    });

        // Yearly Sales Chart
 // Get the current year
const currentYear = new Date().getFullYear();

// Generate an array of years for the labels
const labels = [];
for (let i = 0; i < 3; i++) { // Adjust the number as needed
    labels.push(currentYear + i);
}

// Assuming yearlySalesData is already defined as PHP JSON data
const yearlySalesData = <?php echo $jsonYearlyData; ?>; 
const salesCtx = document.getElementById('salesChart').getContext('2d');

const salesChart = new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: labels, // Use the generated labels
        datasets: [{
            label: 'Sales',
            data: yearlySalesData,
            borderColor: '#a72828',
            
            fill: false
        }]
    },
    options: {
        responsive: false,
        maintainAspectRatio: false
    }
});

 function filterOrdersByDate() {
    const orderDate = document.getElementById('orderDate').value;

    // Make an AJAX request
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'fetchOrders.php', true); 
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (this.status === 200) {
            // Update the table body with the response
            document.getElementById('tableBody').innerHTML = this.responseText;
        }
    };

    xhr.send('order_date=' + encodeURIComponent(orderDate));
}


        </script>

</body>

</html>