<?php
session_start();
// Fetch notifications from the adminnotif_tbl for today only
include '../includes/db_connect.php';

// Get the current date (no time part)
$currentDate = date('Y-m-d');

// Query to fetch notifications for today only
$Notifquery = "SELECT notif_id, notif_message, notif_date, notif_status 
               FROM adminnotif_tbl 
               WHERE DATE(notif_date) = '$currentDate' 
               ORDER BY notif_date DESC";

// Execute the query
$Notifresult = $conn->query($Notifquery);

// Get the count of 'unseen' notifications for today
$notificationCount = $conn->query("SELECT COUNT(*) FROM adminnotif_tbl WHERE notif_status = 'unseen' AND DATE(notif_date) = '$currentDate'")->fetch_row()[0];

$admin_id = $_SESSION['admin_id'];

// Query to check if the admin has 'super_admin' role
$query = "SELECT admin_role FROM admin_tbl WHERE admin_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Check if the admin role is 'super_admin'
$isSuperAdmin = ($row['admin_role'] == 'super_admin');

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <title>Navbar with Logout Confirmation</title>

    <style>
        /* Remove block display for the submenus */
        .dropdown-menu .dropdown-menu {
            display: block;
            padding-left: 20px;
            /* Adjust indentation for submenus */
        }

        /* Remove the dropdown-arrow icon if needed */
        .dropdown-item.dropdown-toggle::after {
            content: none;
        }

        /* Optional: You can add hover styles for a better experience */
        .dropdown-item:hover {
            background-color: #f1f1f1;
        }
    </style>

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <div class="container-fluid">
            <button class="btn btn-toggle" id="menu-toggle-top">☰</button>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <!-- Notification Dropdown -->
                    <li class="nav-item dropdown position-relative">
                        <a class="nav-link" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <!-- Badge for unseen notification count -->
                            <span class="badge badge-danger position-absolute top-1 start-95 translate-middle" id="notification-count" style="font-size: 10px;">
                                <?php echo $notificationCount; ?>
                            </span>
                        </a>
                        <!-- Scrollable and Wider Dropdown Menu with Right Alignment and Margin Right -->
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="max-height: 300px; overflow-y: auto; width: 400px;">
                            <h6 class="dropdown-header">Notifications</h6>
                            <div id="notification-items">
                                <?php if ($Notifresult->num_rows > 0): ?>
                                    <?php while ($row = $Notifresult->fetch_assoc()): ?>
                                        <a href="#" class="dropdown-item text-wrap <?php echo $row['notif_status'] == 'unseen' ? 'font-weight-bold' : ''; ?>"
                                            data-notif-id="<?php echo $row['notif_id']; ?>"
                                            onclick="markAsSeen(this)">
                                            <?php echo $row['notif_message']; ?>
                                            <small class="d-block text-muted"><?php echo date("M d, Y h:i A", strtotime($row['notif_date'])); ?></small>
                                        </a>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="dropdown-item text-center text-muted">No notifications</p>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                                <!--
                                <a href="all_notifications.php" class="dropdown-item text-center text-primary">See all notifications</a> -->
                            </div>
                        </div>
                    </li>
                    <!-- Settings Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="settingsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-gear"></i> Settings
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="settingsDropdown">
                            <!-- Preferences Item with Shop Management as Submenu -->
                            <li class="dropdown-item dropdown-toggle" href="#"> <i class="fas fa-newspaper"></i> Preferences</li>
                            <ul class="dropdown-menu" style="display: none; padding-left: 20px;">
                                <li><a class="dropdown-item" href="../admin/shopmgt.php"> <i class="fas fa-shop"></i> Shop Management</a></li>
                                 <!-- Only display Backup & Restore if the admin is super_admin -->
                                 <?php if ($isSuperAdmin): ?>
                                    <li><a class="dropdown-item" href="../admin/DB_bUp&Restore.php"> <i class="fas fa-database"></i> Backup & Restore</a></li>
                                <?php endif; ?>
                            </ul>

                            <li class="dropdown-item dropdown-toggle" href="#"> <i class="fas fa-gear"></i> Configuration</li>
                            <ul class="dropdown-menu" style="display: none; padding-left: 20px;">
                                <li><a class="dropdown-item" href="../admin/rbac.php"> <i class="fas fa-user-lock"></i> RBAC</a></li>
                                <li><a class="dropdown-item" href="../admin/editRate.php"> <i class="fas fa-calendar-plus"></i> Daily Rates</a></li>
                                <li><a class="dropdown-item" href="../admin/attendanceConfig.php"> <i class="fas fa-clock"></i> In/Out Config</a></li>
                                <li><a class="dropdown-item" href="../admin/brgy.php"> <i class="fas fa-house-medical"></i> BRGY Config</a></li>
                                <li><a class="dropdown-item" href="../admin/configRatings.php"> <i class="fa-solid fa-comment"></i> Customers Feedback</a></li>
                            </ul>
                        </ul>

                    </li>


                    <!-- Profile and Logout Links -->
                    <li class="nav-item">
                        <a class="nav-link" href="#profile"><i class="fas fa-user"></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>




    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('logout-link').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default link action

            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out of your account.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to logout page if confirmed
                    window.location.href = '../includes/logout.php';
                }
            });
        });

        function markAsSeen(notificationElement) {
            var notifId = notificationElement.getAttribute('data-notif-id');

            // Send AJAX request to update the notification status to 'seen'
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../includes/update_notif_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status == 200) {
                    // On success, mark the notification as seen (remove bold text)
                    notificationElement.classList.remove('font-weight-bold');
                    // Optionally, you can update the count of unseen notifications
                    updateNotificationCount();
                }
            };

            xhr.send('notif_id=' + notifId); // Send the notification ID to the server
        }

        function updateNotificationCount() {
            var countElement = document.getElementById('notification-count');

            // Make an AJAX request to get the updated notification count
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '../includes/get_unseen_notif_count.php', true);

            xhr.onload = function() {
                if (xhr.status == 200) {
                    // Update the badge with the new count
                    countElement.innerText = xhr.responseText;
                }
            };

            xhr.send();
        }

        document.querySelectorAll('.dropdown-item.dropdown-toggle').forEach(item => {
    item.addEventListener('click', function (e) {
        const submenu = this.nextElementSibling;
        
        // Close all other submenus
        document.querySelectorAll('.dropdown-menu .dropdown-menu').forEach(otherSubmenu => {
            if (otherSubmenu !== submenu) {
                otherSubmenu.style.display = 'none';
            }
        });

        // Toggle the clicked submenu
        submenu.style.display = (submenu.style.display === 'block' ? 'none' : 'block');
        
        // Prevent the click event from propagating to other elements
        e.stopPropagation();
    });
});

    </script>
</body>

</html>