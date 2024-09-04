
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
            height: 100vh; /* Full viewport height */
        }
        #sidebar-wrapper {
    min-height: 120vh;
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
.container-rate{
    display: flex;
    background-color: white;
    border: 2px solid #a72828;
    width: 50%;
    height: 45vh;
    color: #a72828;
    margin-top: 40px;
    margin-left: 30px;
    border-radius: 10px;
    justify-content: center;
    text-align: center;
    align-items: center;

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

$conn->close();
?>
           <div class="container-box">
                <span class="sales-amount">₱<?php echo number_format($monthly_sales); ?></span>
                <span class="sales-label">Monthly Sales</span>
            </div>
            <div class="container-box">
                 <span class="sales-amount"><?php echo number_format($total_customers); ?></span>
                <span class="sales-label">Customer</span>
            </div>
            <div class="container-box">Staff</div>
            
        </div>
        <br>
        <hr>
        <div class="container-rate"> Rating Card</div>
    
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