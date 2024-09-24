<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
        }
        .bg-danger {
            background-color: red;
            color: white;
        }
    </style>
</head>
<body>
    <nav id="sidebar" class="bg-dark">
        <div class="profile">
            <img src="../img/aye.jpg" alt="Profile Picture">
            <h5>Ayeng Dohinog</h5>
            <p class="role">Order Manager</p>
        </div>
        <a href="../ordr_manager/order_manager.php" class="text-light active1">
            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
        </a>
        <a href="../ordr_manager/trackOrders.php" class="text-light active2">
            <i class="fas fa-box"></i> <span>Orders</span>
        </a>
        <a href="../ordr_manager/trackShipper.php" class="text-light active3">
            <i class="fas fa-truck-fast"></i> <span>Shipper</span>
        </a>
        <a href="../ordr_manager/ctom.php" class="text-light active4" id="customersLink">
            <i class="fas fa-users"></i> <span>Customers</span>
            <span class="badge bg-danger" id="unreadMessagesBadge" style="display:none;"></span>
        </a>
        <a href="../ordr_manager/reports.php" class="text-light active5">
            <i class="fas fa-chart-line"></i> <span>Reports</span>
        </a>
        <a href="#" class="text-light active6">
            <i class="fas fa-gear"></i> <span>Settings</span>
        </a>

        <!-- Logout link at the bottom -->
        <a href="#" class="text-light logout" id="logoutBtn">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
        </a>
    </nav>

    <script>
        // Fetch unread messages count using fetch API
        function fetchUnreadMessages() {
            fetch('../includes/get_unread_messages.php')
                .then(response => response.json())
                .then(data => {
                    const unreadCount = data.unread_count;
                    const badge = document.getElementById('unreadMessagesBadge');

                    if (unreadCount > 0) {
                        badge.textContent = unreadCount;
                        badge.style.display = 'inline-block'; // Show the badge if there are unread messages
                    } else {
                        badge.style.display = 'none'; // Hide the badge if no unread messages
                    }
                })
                .catch(error => console.error('Error fetching unread messages:', error));
        }

        // Poll the server every 10 seconds to check for unread messages
        setInterval(fetchUnreadMessages, 5000);

        // When the "Customers" link is clicked, hide the unread messages badge
        document.getElementById('customersLink').addEventListener('click', function() {
            var badge = document.getElementById('unreadMessagesBadge');
            if (badge) {
                badge.style.display = 'none'; // Hides the badge visually without affecting the database
            }
        });

        // Call fetchUnreadMessages to update the unread messages badge on page load
        window.onload = fetchUnreadMessages;
    </script>
</body>
</html>
