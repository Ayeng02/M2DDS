<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        /* Sidebar on the left */
        #sidebar {
            height: 100vh;
            width: 200px;
            background: linear-gradient(380deg,  #ff83259b, #a72828, #343a4043, #343a40af); /* Gradient background */
            backdrop-filter: 100px;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
        }

        #sidebar a {
            color: #fff;
            padding: 15px;
            display: block;
            text-decoration: none;
            transition: background 1s ease;
        }

        .active{
            background: linear-gradient(180deg, #ff83259b, #a72828);
        }

        #sidebar a:hover {
            background: linear-gradient(180deg, #ff83259b, #a72828);
        }

        #sidebar.collapsed {
            width: 80px;
        }

        #sidebar.collapsed a {
            text-align: center;
            padding: 10px;
        }

        #sidebar.collapsed a span {
            display: none;
        }

        /* Profile section */
        .profile {
            margin: 0 0 20px 0;
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #495057;
        }

        .profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .profile h5 {
            color: #fff;
            margin: 0;
        }

        .profile .role {
            color: #adb5bd;
            /* Light grey color for role */
            font-size: 0.9rem;
            /* Slightly smaller font size for role */
            margin: 0;
        }

        /* Logout at the bottom */
        .logout {
            margin-top: auto;
            padding-bottom: 20px;
        }

        /* Sidebar toggle button (visible only on small screens) */
        .toggle-btn {
            position: fixed;
            top: 20px;
            left: 250px;
            z-index: 1100;
            cursor: pointer;
            display: none;
            transition: all 0.3s;
        }

        #sidebar.collapsed~.toggle-btn {
            left: 80px;
        }

        /* Main content */
        .content {
            margin-left: 200px;
            padding: 20px;
            transition: margin-left 0.3s;
        }

        /* Adjust content margin when sidebar is collapsed */
        #sidebar.collapsed~.content {
            margin-left: 80px;
        }

        /* Clock container */
        #clock-container {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 10px 20px;
            margin-bottom: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #clock {
            font-size: 2rem;
            font-weight: bold;
        }

        #date {
            margin-left: 10px;
            font-size: 1.2rem;
            color: #6c757d;
        }

        /* Container for status cards */
        .status-container {
            display: flex;
            justify-content: center;
            /* Center cards horizontally */
            align-items: center;
            /* Center cards vertically */
            gap: 20px;
            /* Adjust gap between cards */
            flex-wrap: wrap;
            /* Allow cards to wrap to next line on smaller screens */
        }

        /* Status card styling */
        .status-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 15px;
            border-radius: 5px;
            color: #fff;
            background-color: #007bff;
            /* Default color, will change based on status */
            height: 150px;
            /* Fixed height */
            width: 220px;
            /* Fixed width */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            /* Optional shadow */
            font-size: 1rem;
            /* Consistent font size */
            transition: all 0.3s ease-in-out;
        }

        .status-card:hover{
            transform: scale(1.05);
        }

        .status-card i {
            font-size: 2rem;
            /* Larger icon size */
            margin-bottom: 10px;
            /* Space between icon and text */
        }

        .status-text {
            font-size: 1.2rem;
            /* Text size for status */
            margin-bottom: 5px;
            /* Space between status text and number */
        }

        .status-number {
            font-size: 1.5rem;
            /* Font size for number */
            font-weight: bold;
            /* Bold text for emphasis */
            color: #fff;
            /* White text color for contrast */
        }

        /* For Webkit browsers */
::-webkit-scrollbar {
    width: 12px; /* Width of the scrollbar */
}

::-webkit-scrollbar-track {
    background: #f1f1f1; /* Color of the track */
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #ff83259b, #a72828);
    border-radius: 10px; /* Rounded corners of the scrollbar thumb */
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(380deg, #a72828, #343a40);
}


        /* Navbar collapse on small screens */
        @media (max-width: 768px) {
            #sidebar {
                display: none;
            }

            .toggle-btn {
                display: block;
            }

            .content {
                margin-left: 0;
            }

            #mobile-nav {
                display: block;
            }

        }

        @media (min-width: 768px) {
            #mobile-nav {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .status-card {
                width: 150px;
            }
        }
    </style>
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
        <a href="#" class="text-light logout">
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
    </script>
</body>

</html>