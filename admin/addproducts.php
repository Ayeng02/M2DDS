
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
    min-height: 100vh;
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
.addprod-content{
    display: flex;
    border: 1px solid #8c1c1c;
    width: 40%;
    height: 20vh;
    border-radius: 10px;
    margin-left: 15rem;
    margin-top: 5rem;
    text-align: center;
    justify-content: center;
    align-items: center;
    font-size: 40px;
    font-weight: bold;
    color: #8c1c1c;
    background-color: #FF8225;
      text-shadow: 0 2px 7px #a72828;

}

.update-btn{
    display: flex;
    float: right;
    margin-right: 15rem;
    margin-top: 20px;
    width: 150px;
    height: 50px;
    justify-content: center;
    text-align: center;
    align-items: center;
    font-size: 25px;
    background-color: green;
    border: 2px solid black;
    border-radius: 20px;
    color: white;
}
.product-table-container {
    display: flex;
    border: 1px solid #a72828;
    width: 80%;
    height: 50vh;
    margin-top: 40px;
    margin-left: 5rem;
    border-radius: 10px;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    flex-direction: column;
}

.product-table {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.product-table h2 {
    margin-bottom: 10px;
    color: #007bff;
    text-align: center;
}

.product-table table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed; /* Ensures consistent column width */
}

.product-table thead {
    background-color: #007bff;
    color: white;
    font-size: 16px;
    text-transform: uppercase;
    text-align: center;
     height: calc(100% - 45px); 
}

.product-table tbody {
    display: block;
    overflow-y: auto; /* Enables scrolling for the tbody */
    height: calc(100% - 45px); /* Adjust height according to the container */
}

.product-table thead, .product-table tbody tr {
    display: table;
    width: 100%; /* Ensures the header and rows take up full width */
    table-layout: fixed; /* Ensures the columns have a consistent width */
}

.product-table th, .product-table td {
    padding: 12px;
    border: 1px solid #dee2e6;
    text-align: center;
    white-space: nowrap;
    font-size: 16px;
}

.product-table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

.product-table tbody tr:hover {
    background-color: #e2e6ea;
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
                <h1 class="mt-4" id="dashboard">Products</h1>
            
            </div>
            <button class="addprod-content">
                ADD PRODUCT
            </button>
            <br>
            <hr>

            <?php 
            $sql = "SELECT prod_code, category_code, prod_name, prod_price, prod_discount, prod_qoh FROM product_tbl";
            $result = $conn->query($sql);

             $products = [];

             if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                 $products[] = array(
                     "prod_code" => $row['prod_code'],
                     "category_code" => $row['category_code'],
                    "prod_name" => $row['prod_name'],
                    "prod_price" => $row['prod_price'],
                    "prod_qoh" => $row['prod_qoh'],
                    "prod_discount" => $row['prod_discount']
                );
            }
        } else {
            $products = [];
        }
            ?>
            <div class="product-table-container">
            <div class="product-table">
              <span>Top Products</span>

            <table>
        <thead>
                <th>Product Code</th>
                <th>Category Code</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Discount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?php echo htmlspecialchars($product['prod_code']); ?></td>
                <td><?php echo htmlspecialchars($product['category_code']); ?></td>
                <td><?php echo htmlspecialchars($product['prod_name']); ?></td>
                <td><?php echo number_format($product['prod_price']); ?></td>
                 <td><?php echo number_format($product['prod_qoh']); ?></td>
                 <td><?php echo number_format($product['prod_discount']); ?></td>
                
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
            </div>
            </div>
            <button class="update-btn">UPDATE</button>

         

       
    
    
   
       
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