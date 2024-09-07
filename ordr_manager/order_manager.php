<?php
session_start();

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
    <title>Order Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/ordr_css.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <!-- Sidebar on the left -->
    <nav id="sidebar" class="bg-dark">
        <div class="profile">
            <img src="../img/aye.jpg" alt="Profile Picture">
            <h5>Ayeng Dohinog</h5>
            <p class="role">Order Manager</p>
        </div>
        <a href="#" class="text-light active">
            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
        </a>
        <a href="#" class="text-light">
            <i class="fas fa-box"></i> <span>Orders</span>
        </a>
        <a href="#" class="text-light">
            <i class="fas fa-users"></i> <span>Customers</span>
        </a>
        <a href="#" class="text-light">
            <i class="fas fa-chart-line"></i> <span>Reports</span>
        </a>

        <!-- Logout link at the bottom -->
        <a href="#" class="text-light logout" id="logoutBtn">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
        </a>
    </nav>


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
            <h2 class="my-4">Total Order Statuses</h2>
            <div class="status-container">
                <div class="status-card bg-warning">
                    <i class="fas fa-clock"></i>
                    <div class="status-text">Pending</div>
                    <div class="status-number">12</div> <!-- Example number -->
                </div>
                <div class="status-card bg-primary">
                    <i class="fas fa-cogs"></i>
                    <div class="status-text">Processing</div>
                    <div class="status-number">8</div> <!-- Example number -->
                </div>
                <div class="status-card bg-success">
                    <i class="fas fa-truck"></i>
                    <div class="status-text">Shipped</div>
                    <div class="status-number">15</div> <!-- Example number -->
                </div>
                <div class="status-card bg-info">
                    <i class="fas fa-check-circle"></i>
                    <div class="status-text">Delivered</div>
                    <div class="status-number">22</div> <!-- Example number -->
                </div>
                <div class="status-card bg-danger">
                    <i class="fas fa-times-circle"></i>
                    <div class="status-text">Canceled</div>
                    <div class="status-number">5</div> <!-- Example number -->
                </div>
            </div>
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
    </div>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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



        document.getElementById('logoutBtn').addEventListener('click', function (e) {
        e.preventDefault(); // Prevent default anchor behavior

        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                // Send a POST request to log out
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'logout=true'
                }).then(response => {
                    // Redirect to the login page after successful logout
                    window.location.href = '../login.php';
                });
            }
        });
    });


    </script>
</body>

</html>