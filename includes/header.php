<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../customer/login.php");
    exit;
}

// Include database connection
include 'db_connect.php';

// Check if customer_id is set in session
if (!isset($_SESSION['cust_id'])) {
    echo "Error: Customer ID not set in session.";
    exit;
}

// Retrieve the logged-in user's information
$customer_id = $_SESSION['cust_id'];
$sql = "SELECT * FROM customers WHERE cust_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

// Check if customer data is retrieved
if (!$customer) {
    echo "Error: Customer not found.";
    exit;
}

// Get cart item count
$sql_cart = "SELECT COUNT(*) as item_count FROM cart_table WHERE cust_id = ?";
$stmt_cart = $conn->prepare($sql_cart);
$stmt_cart->bind_param("s", $customer_id);
$stmt_cart->execute();
$result_cart = $stmt_cart->get_result();
$cart_data = $result_cart->fetch_assoc();
$item_count = $cart_data['item_count'];

// Define the time range for the last 24 hours
date_default_timezone_set('Asia/Manila'); // Set timezone to Philippine Time
$now = date('Y-m-d H:i:s');
$one_day_ago = date('Y-m-d H:i:s', strtotime('-1 day'));


// Retrieve notifications from the last 24 hours
$sql_notifications = "SELECT * FROM notifications WHERE cust_id = ? AND created_at BETWEEN ? AND ? ORDER BY created_at DESC";
$stmt_notifications = $conn->prepare($sql_notifications);
$stmt_notifications->bind_param("sss", $customer_id, $one_day_ago, $now);
$stmt_notifications->execute();
$result_notifications = $stmt_notifications->get_result();
$notifications = $result_notifications->fetch_all(MYSQLI_ASSOC);

// Calculate the number of unread notifications
$unread_count = array_reduce($notifications, function ($count, $notification) {
    return $notification['status'] === 'unread' ? $count + 1 : $count;
}, 0);

// Handle logout request
if (isset($_POST['logout']) && $_POST['logout'] === 'true') {
    // Destroy all session data
    session_unset();
    session_destroy();

    // Redirect to the index page
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <!-- navbar.php -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="#">
            <img class="logo" src="../img/logo.ico" alt="">
            Meat-To-Door
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link act1" href="customerLandingPage.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link act2" href="products.php">
                        <i class="fas fa-box-open"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link act3" href="orders.php">
                        <i class="fas fa-receipt"></i> Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link act4" href="profile.php">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link act6" href="cart.php">
                        <i class="fas fa-cart-arrow-down"></i>
                        <span class="badge badge-primary cart-badge"><?php echo $item_count; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <div class="nav-link notification-bell" onclick="toggleNotifications();">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" id="notificationBadge"><?php echo $unread_count; ?></span>
                        <div class="notification-overlay" id="notificationOverlay">
                            <?php if (empty($notifications)): ?>
                                <div class="notification-item">No new notifications</div>
                            <?php else: ?>
                                <?php foreach ($notifications as $notification): ?>
                                    <div id="notification-<?php echo $notification['id']; ?>" class="notification-item<?php echo $notification['status'] === 'read' ? ' read' : ''; ?>" data-id="<?php echo $notification['id']; ?>">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                        <div class="notification-date">
                                            <?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="javascript:void(0);" onclick="confirmLogout();">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
            <form class="form-inline my-2 my-lg-0 ml-auto" action="./search_results.php" method="GET">
                <input class="fc1 form-control mr-sm-2" type="search" name="v" placeholder="Search for products..." aria-label="Search">
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
            </form>

        </div>
    </nav>

    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out of your account.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {

                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '../includes/header.php';

                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'logout';
                    input.value = 'true';
                    form.appendChild(input);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function markNotificationAsRead(notificationId) {
            fetch('../includes/mark_notification.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'id': notificationId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the UI to reflect the read status
                        const notificationElement = document.querySelector(`#notification-${notificationId}`);
                        notificationElement.classList.add('read');

                        // Update the notification badge count
                        const badge = document.getElementById('notificationBadge');
                        let currentCount = parseInt(badge.textContent);

                        // Decrement the badge count but ensure it doesn't go below 0
                        badge.textContent = Math.max(currentCount - 1, 0);
                    } else {
                        console.error('Failed to mark notification as read:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }


        function toggleNotifications() {
            const overlay = document.getElementById('notificationOverlay');
            overlay.classList.toggle('show');
        }

        // Attach click event to notification items
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                markNotificationAsRead(notificationId);
            });
        });
    </script>
</body>

</html>