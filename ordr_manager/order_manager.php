<?php
session_start();
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

// Query to fetch recent "Pending" orders
$pendingQuery = "
    SELECT o.order_id, o.prod_code, o.order_fullname, o.order_phonenum, 
           CONCAT(o.order_purok, ', ', o.order_barangay, ', ', o.order_province) AS order_address, 
           o.order_qty, o.order_total, o.order_date, p.prod_name 
    FROM order_tbl o
    JOIN product_tbl p ON o.prod_code = p.prod_code 
    WHERE status_code = 1 AND DATE(o.order_date) = CURDATE()
    ORDER BY order_date DESC";

$pendingResult = $conn->query($pendingQuery);

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
if ($statusResult && $statusResult->num_rows > 0) {
    while ($row = $statusResult->fetch_assoc()) {
        $statusCounts[$row['status_name']] = $row['total_orders'];
    }
} else {

}
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
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/ordr_css.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    
    <!-- SweetAlert2 JS -->
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
        <div class="container-fluid mt-4">
    <h2 class="mb-4">Recent Pending Orders</h2>
    <div class="d-flex justify-content-start align-items-center mb-3">
        <input type="number" id="processingNumber" placeholder="Enter Number" class="form-control me-2 w-auto" />
        <button class="btn accBtn" id="acceptBtn">Accept Order(s)</button>
    </div>
    <div class="table-responsive overflow-auto">
        <table class="table table-bordered table-striped" id="ordersTable">
            <thead>
                <tr>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th style="background-color: #ce3434bd; color: white;">Order ID</th>
                    <th style="background-color: #ce3434bd; color: white;">Product</th>
                    <th style="background-color: #ce3434bd; color: white;">Full Name</th>
                    <th style="background-color: #ce3434bd; color: white;">Phone Number</th>
                    <th style="background-color: #ce3434bd; color: white;">Address</th>
                    <th style="background-color: #ce3434bd; color: white;">Quantity</th>
                    <th style="background-color: #ce3434bd; color: white;">Total</th>
                    <th style="background-color: #ce3434bd; color: white;">Date</th>
                </tr>
            </thead>
            <tbody>
                <!-- PHP to populate table rows -->
                <?php if ($pendingResult->num_rows > 0): ?>
                    <?php while ($row = $pendingResult->fetch_assoc()): ?>
                        <tr>
                            <td><input type="checkbox" class="orderCheckbox" value="<?php echo htmlspecialchars($row['order_id']); ?>"></td>
                            <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['prod_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['order_fullname']); ?></td>
                            <td><?php echo htmlspecialchars($row['order_phonenum']); ?></td>
                            <td><?php echo htmlspecialchars($row['order_address']); ?></td>
                            <td><?php echo htmlspecialchars($row['order_qty']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($row['order_total'], 2)); ?></td>
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

        $(document).ready(function() {
    // Initialize DataTables with pagination and set page length to 10
    $('#ordersTable').DataTable({
        "pageLength": 10,
        "ordering": true,
        "autoWidth": false,
        "responsive": true
    });

    // Check All Checkbox functionality
    $('#checkAll').on('change', function() {
        var checkboxes = $('.orderCheckbox');
        checkboxes.prop('checked', this.checked);
    });

    // Accept Button functionality
    $('#acceptBtn').on('click', function() {
        const selectedOrders = [];
        const checkboxes = $('.orderCheckbox:checked');
        const totalOrders = $('.orderCheckbox');
        const processingNumber = parseInt($('#processingNumber').val(), 10); // Ensure it's a number

        // Validate the processingNumber
        if (processingNumber > 0) {
            // Automatically select the first `n` checkboxes based on input
            for (let i = 0; i < processingNumber && i < totalOrders.length; i++) {
                if (!totalOrders[i].checked) {
                    totalOrders[i].checked = true; // Mark the first `n` orders
                    selectedOrders.push(totalOrders[i].value);
                }
            }
        } else {
            // Use manually checked boxes if processingNumber is not valid
            checkboxes.each(function() {
                selectedOrders.push($(this).val());
            });
        }

        // Validate if `selectedOrders` is not empty
        if (selectedOrders.length > 0) {
            // Send selected order IDs to the server using AJAX
            fetch('update_PenStatus.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    order_ids: selectedOrders,
                    processing_number: processingNumber // Include processing_number if needed
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
                        text: 'Order(s) updated to Processing!',
                        icon: 'success'
                    }).then(() => {
                        location.reload(); // Ensure SweetAlert2 is working before reloading
                    });
                } else {
                    Swal.fire('Error', 'Failed to update orders: ' + data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'An error occurred while updating orders.', 'error');
            });
        } else {
            Swal.fire('Warning', 'Please select or specify the number of orders to accept.', 'warning');
        }
    });
});



document.addEventListener('DOMContentLoaded', function() {
        const statusCards = document.querySelectorAll('.status-number');

        function animateNumbers() {
            statusCards.forEach(card => {
                // Add animation class
                card.classList.add('animate');

                // Remove the class after the animation ends
                card.addEventListener('animationend', function() {
                    card.classList.remove('animate');
                }, { once: true });
            });
        }

        // Trigger animation on page load
        animateNumbers();
    });

     // Track the last order ID or timestamp
     let lastOrderId = null;

function checkForNewOrders() {
    fetch('check_new_orders.php')
        .then(response => response.json())
        .then(data => {
            // Assuming 'latest_order_id' is the field returned by the server
            if (data.latest_order_id && data.latest_order_id !== lastOrderId) {
                lastOrderId = data.latest_order_id;
                window.location.reload(); // Refresh the page if new orders are found
            }
        })
        .catch(error => console.error('Error:', error));
}

// Check for new orders every 1 minute (60000 milliseconds)
setInterval(checkForNewOrders, 60000);

    

    </script>
</body>

</html>