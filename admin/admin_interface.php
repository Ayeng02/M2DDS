
<?php 
session_start();
include '../includes/db_connect.php';
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
            height: 100%; /* Full viewport height */
        }
        #sidebar-wrapper {
    min-height: 100%;
    width: 80px; /* Default width for icons only */
    background-color: #a72828;
    color: #fff;
    transition: width 0.3s ease;
    overflow-y: auto; /* Allow vertical scrolling */
    position: relative;
    overflow-x: hidden; /* Prevent horizontal scrolling */
    border-right: 1px solid #ddd; /* Light border to separate from content */
    box-shadow: 2px 0 5px rgba(0,0,0,0.1); /* Subtle shadow */
}
#sidebar-wrapper.expanded {
    width: 250px; /* Expanded width */
}
#sidebar-wrapper .sidebar-heading {
    padding: 1rem;
    display: flex;
    align-items: center;
    background-color: #FF8225;
    color: #fff;
    border-bottom: 1px solid #ddd; /* Border for separation */
}
#sidebar-wrapper .logo-img {
    width: 40px; /* Adjust size as needed */
    height: 40px;
    margin-right: 10px; /* Space between logo and text */
}
#sidebar-wrapper .sidebar-title {
    font-size: 1.5rem;
    display: inline; /* Ensure title is always visible */
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
            border-radius: 0; /* Remove default border radius */
            transition: background-color 0.2s ease; /* Smooth hover effect */
        }
        #sidebar-wrapper .list-group-item i {
            font-size: 1.5rem;
            margin-right: 15px;
        }
        #sidebar-wrapper .list-group-item span {
    display: none; /* Hide text in default state */
    margin-left: 10px;
    white-space: nowrap; /* Prevent text wrapping */
}
#sidebar-wrapper.expanded .list-group-item span {
    display: inline; /* Show text in expanded state */
}
        #sidebar-wrapper .list-group-item:hover {
            background-color: #8c1c1c; /* Darker color on hover */
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
            box-shadow: 0 0 5px rgba(0,0,0,0.2); /* Button shadow */
        }
        #sidebar-wrapper .toggle-btn:hover {
            background-color: #a72828;
        }
        #page-content-wrapper {
            flex: 1;
            padding: 20px;
            transition: margin-left 0.3s ease;
            background-color: #f8f9fa; /* Slightly different background */
        }
        #page-content-wrapper.sidebar-expanded {
            margin-left:0px; /* Match the expanded sidebar width */
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
    width: 30px; /* Adjust size when collapsed */
    height: 30px;
}
/*.container-fluid-header{
    display: flex;
    background-color: #FF8225;
}*/
.container-box{
    display: inline-flex;
    flex-direction: column;
    background-color: white;
    border: 2px solid #FF8225;
    width: 30%;
    height: 35vh;
    color: #FF8225;
    border-radius: 10px;
    align-items: center;
    margin-top: 20px;
    margin-left: 30px;
    text-align: center;
    justify-content: center;
    
}
.sales-amount {
    font-size: 3rem;
    font-weight: bold; 
}
.sales-label {
    font-size: 1.5rem;
    margin-top: 10px; 
}
.rating_scale-container{
    display: flex;
}
.container-rate{
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
.scale-nav{
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
.scale-nav span{
    color: #8c1c1c;
    font-size: 50px;
    margin-bottom: 10px;
    font-weight: 700;
    text-shadow: 0 8px 10px #8c1c1c;
}
 #chartContainer {
    height: 100%; 
    width: 100%;
    }
        canvas {
            width: 85% !important; 
        }
 table {
            width: 100%;
            margin: 0 auto;
            border-collapse: collapse;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background-color: #fff;
            border-radius: 30px;
            border-radius: 10px;
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 12px 15px;
            text-align: left;
            transition: background-color 0.3s;
            
        }

        th {
            background-color: #8c1c1c;
            color: #fff;
            text-transform: uppercase;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
        }

        td {
            background-color: #f9f9f9;
            text-align: center;
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
   
    while($row = $result->fetch_assoc()) {
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
    while($row = $result->fetch_assoc()) {
        $total_customers = $row['total_customers'];
    }
} else {
    $total_customers = "No data available";
}


?>
           <div class="container-box">
                <span class="sales-amount">₱<?php echo number_format($monthly_sales); ?></span>
                <span class="sales-label">Monthly Sales</span>
            </div>
            <div class="container-box">
                 <span class="sales-amount"><?php echo number_format($total_customers); ?></span>
                <span class="sales-label">Customer</span>
            </div>
            <div class="container-box">
                <span class="sales-amount"><?php echo number_format($total_customers); ?></span>
                <span class="sales-label">Employee</span>
            </div>
            
        </div>
        <br>
        <hr>
        <?php 
        $sql = "SELECT p.prod_name, SUM(o.order_qty) AS total_purchased 
        FROM order_tbl o
        JOIN product_tbl p ON o.prod_code = p.prod_code
        WHERE YEAR(o.order_date) = YEAR(CURDATE())
         AND o.status_code = '4'
        GROUP BY p.prod_name";
            $result = $conn->query($sql);
            $dataPoints = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $dataPoints[] = array(
                        "label" => $row['prod_name'],
                        "y" => $row['total_purchased']
                    );
                }
            } else {
                $dataPoints = array(
                    array("label" => "No data", "y" => 0)
                );
            }
        ?>

        <div class="rating_scale-container">
        <div class="container-rate">
            <div id="chartContainer" ></div>
    </div>
     <script>
    window.onload = function() {
        var chart = new CanvasJS.Chart("chartContainer", {
            animationEnabled: true,
            title: {
                text: "Product Purchases for the Current Year"
            },
            subtitles: [{
                text: "Current Year"
            }],
            data: [{
                type: "pie",
                yValueFormatString: "#,##0.00\"%\"",
                indexLabel: "{label} ({y})",
                dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
            }]
        });
        chart.render();
    }
    </script>
    <?php
     $sql = "SELECT p.prod_name, SUM(o.order_qty) AS total_purchased 
        FROM order_tbl o
        JOIN product_tbl p ON o.prod_code = p.prod_code
        WHERE YEAR(o.order_date) = YEAR(CURDATE())
         AND o.status_code = '4'
        GROUP BY p.prod_name
        ORDER BY total_purchased DESC";
        $result = $conn->query($sql);

        $topProducts = [];

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                 $topProducts[] = array( 
                    "prod_name" => $row['prod_name'],
                    "order_qty" => $row['total_purchased']
                );
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
                <th>Product Name</th>
                <th>Quantity Purchased</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($topProducts as $product): ?>
            <tr>
                <td><?php echo htmlspecialchars($product['prod_name']); ?></td>
                <td><?php echo number_format($product['order_qty']); ?></td>
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