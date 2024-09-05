<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
</head>
<body>
    <!-- Sidebar -->
    <div id="sidebar-wrapper">
        <div class="sidebar-heading">
            <img src="../img/logo.ico" alt="Logo" class="logo-img">
            <span class="sidebar-title">Admin Panel</span>
        </div>
        <div class="list-group list-group-flush">
            <a href="admin_interface.php" class="list-group-item list-group-item-action">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="addproducts.php" class="list-group-item list-group-item-action">
                <i class="fas fa-box"></i>
                <span>Products</span>
            </a>
            <a href="#categories" class="list-group-item list-group-item-action">
                <i class="fas fa-th-large"></i>
                <span>Categories</span>
            </a>
            <a href="#orders" class="list-group-item list-group-item-action">
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
            </a>
             <a href="#orders" class="list-group-item list-group-item-action">
                <i class="fas fa-user"></i>
                <span>Employee</span>
            </a>
        </div>
        <button class="toggle-btn" id="menu-toggle">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    <!-- /#sidebar-wrapper -->
</body>
</html>