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
            color: white;
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
    </style>
</head>
<body>

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div id="sidebar-wrapper">
        <div class="sidebar-heading">
            <img src="../img/logo.ico" alt="Logo" class="logo-img">
            <span class="sidebar-title">Admin Panel</span>
        </div>
        <div class="list-group list-group-flush">
            <a href="#dashboard" class="list-group-item list-group-item-action">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="#products" class="list-group-item list-group-item-action">
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
        </div>
        <button class="toggle-btn" id="menu-toggle">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
            <div class="container-fluid">
                <button class="btn btn-toggle" id="menu-toggle-top">☰</button>
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="#profile">Admin Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#logout">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="content-header">
                <h1 class="mt-4" id="dashboard">Dashboard</h1>
                <p>Welcome to your admin panel. Here you can manage products, categories, and orders.</p>
            </div>

            <div class="content-header">
                <h2 class="mt-4" id="products">Products</h2>
                <p>Manage your products here. You can add, edit, or delete products.</p>
                <div class="card">
                    <div class="card-body">
                        <!-- Product Management UI -->
                        <p>Product management tools and tables go here.</p>
                    </div>
                </div>
            </div>

            <div class="content-header">
                <h2 class="mt-4" id="categories">Categories</h2>
                <p>Manage your categories here. You can add, edit, or delete categories.</p>
                <div class="card">
                    <div class="card-body">
                        <!-- Category Management UI -->
                        <p>Category management tools and tables go here.</p>
                    </div>
                </div>
            </div>

            <div class="content-header">
                <h2 class="mt-4" id="orders">Orders</h2>
                <p>View and manage orders here.</p>
                <div class="card">
                    <div class="card-body">
                        <!-- Order Management UI -->
                        <p>Order management tools and tables go here.</p>
                    </div>
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