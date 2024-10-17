<?php
include '../includes/sf_getEmpInfo.php'; // Existing employee info retrieval

$shipper_id = $_SESSION['emp_id'];

// Redirect to landing page if already logged in
if (isset($_SESSION['EmpLogExist']) && $_SESSION['EmpLogExist'] === true || isset($_SESSION['AdminLogExist']) && $_SESSION['AdminLogExist'] === true) {
    if (isset($_SESSION['emp_role'])) {
        // Redirect based on employee role
        switch ($_SESSION['emp_role']) {
            case 'Cashier':
                header("Location: ../cashier/cashier.php");
                exit;
            case 'Admin':
                header("Location: ../admin/admin_interface.php");
                exit;
            case 'Order Manager':
                header("Location: ../ordr_manager/order_manager.php");
                exit;    
            default:
                // Handle unknown roles or add default redirection if needed
                break;
        }
    }
} else {
    header("Location: ../login.php");
    exit;
}


// Query to get distinct barangays associated with the shipper's assigned orders
$barangayQuery = "
    SELECT DISTINCT o.order_barangay
    FROM delivery_transactbl d
    JOIN order_tbl o ON d.order_id = o.order_id
    WHERE d.shipper_id = '$shipper_id'
      AND d.transact_status = 'Ongoing'
";

$barangayResult = mysqli_query($conn, $barangayQuery);


$selected_barangay = isset($_POST['barangay']) ? $_POST['barangay'] : '';

// Modified query
$ordersQuery = "
    SELECT 
        o.cust_id,
        CONCAT(c.f_name, ' ', c.l_name) AS cust_name,
        CONCAT(o.order_purok, ' ', o.order_barangay, ' ', o.order_province) AS deliv_address,
        o.prod_code,
        o.order_id,
        o.order_date,
        o.status_code,
        o.order_barangay,
        d.transact_status,
        c.phone_num,
        p.prod_name,
        COUNT(o.order_id) AS total_items,
        SUM(o.order_total) + b.Brgy_df AS total_amount,
        GROUP_CONCAT(p.prod_name) AS products,
        b.Brgy_df 
    FROM 
        delivery_transactbl d
    JOIN 
        order_tbl o ON d.order_id = o.order_id
    JOIN 
        brgy_tbl b ON o.order_barangay = b.brgy_name
    JOIN
        customers c ON o.cust_id = c.cust_id
    JOIN
        product_tbl p ON o.prod_code = p.prod_code
    WHERE 
        d.shipper_id = '$shipper_id' 
        AND d.transact_status = 'Ongoing'
        " . ($selected_barangay ? "AND o.order_barangay = '$selected_barangay'" : "") . "
    GROUP BY 
        o.cust_id, o.order_barangay, o.order_date
    ORDER BY 
        o.order_date DESC;
";

$result = mysqli_query($conn, $ordersQuery);
$orderCount = mysqli_num_rows($result); // Count the number of orders
$hasOngoingOrders = ($orderCount > 0);

// Handle AJAX request to update emp_status
if (isset($_POST['emp_status'])) {
    $new_status = $_POST['emp_status'];

    // Validate the status value
    if ($new_status === 'Active' || $new_status === 'On Shipped') {
        // Update the emp_status in the database
        $sql = "UPDATE emp_tbl SET emp_status = ? WHERE emp_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $new_status, $emp_id);

        if ($stmt->execute()) {
            echo "Status updated to $new_status!";
        } else {
            echo "Error updating status: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Invalid status value.";
    }

    $conn->close();
    exit;
}

// Fetch current emp_status to display on the profile widget
$sql = "SELECT * FROM emp_tbl WHERE emp_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result1 = $stmt->get_result();

if ($result1->num_rows > 0) {
    $row = $result1->fetch_assoc();
    $emp_fullname = $row['emp_fname'] . ' ' . $row['emp_lname'];
    $emp_email = $row['emp_email'];
    $emp_num = $row['emp_num'];
    $emp_address = $row['emp_address'];
    $emp_role = $row['emp_role'];
    $emp_status = $row['emp_status'];
    $emp_img = $row['emp_img'];
} else {
    echo "Employee not found.";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipper Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">



    <link rel="stylesheet" href="../css/shipper.css">

</head>

<body>
    <div class="container mt-5">
        <!-- Profile Widget HTML -->
        <div class="profile-card mb-4">
            <div class="d-flex align-items-center">
                <div class="profile-circle">
                    <img src="<?php echo '../' . $emp_img; ?>" alt="Profile Image" class="img-fluid rounded-circle" />
                </div>
                <div class="ml-3 text-center">
                    <h5 class="mb-0"><?php echo $emp_fullname; ?></h5>
                    <p class="mb-0 mt-0" style="color: #FF8225;">
                        <i class="fas fa-truck"></i> <?php echo $emp_role; ?>
                    </p>
                </div>
            </div>
            <div>
                <a href="#" class="text-white" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>

            </div>
        </div>


        <div class="d-flex justify-content-between align-items-center mt-3" style="margin-bottom: 10px;">
            <div class="switch">
                <input type="radio" id="activeSwitch" name="emp_status" value="Active"
                    <?php echo $emp_status === 'Active' ? 'checked' : ''; ?>
                    <?php echo $hasOngoingOrders ? 'disabled' : ''; ?>>
                <label for="activeSwitch" class="switch-label">
                    <span class="switch-inner"></span>
                    <span class="switch-slider"></span>
                </label>
                <span class="switch-text">Active</span>
            </div>
            <div class="switch">
                <input type="radio" id="shippedSwitch" name="emp_status" value="On Shipped"
                    <?php echo $emp_status === 'On Shipped' ? 'checked' : ''; ?>
                    <?php echo $hasOngoingOrders ? 'disabled' : ''; ?>>
                <label for="shippedSwitch" class="switch-label">
                    <span class="switch-inner"></span>
                    <span class="switch-slider"></span>
                </label>
                <span class="switch-text">On Shipped</span>
            </div>
            <!-- Add download button -->
            <button class="btn btn-success d-flex align-items-center" onclick="window.location.href='dtr.php';">
                <i class="bi bi-calendar3" style="font-size: 1rem; margin-right: 5px;"></i>
                DTR
            </button>
        </div>




        <!-- Flex Container for Cards -->
        <div class="card-container">
            <a href="shipper.php" class="custom-card active" style="text-decoration: none;">
                <div class="icon-text-container">
                    <i class="fas fa-home card-icon"></i>
                    <h5>Home</h5>
                </div>
            </a>
            <a href="transaction.php" class="custom-card" style="text-decoration: none;">
                <div class="icon-text-container">
                    <i class="fas fa-exchange-alt card-icon"></i>
                    <h5>Transaction</h5>
                </div>
            </a>
        </div>

        <!-- ORDERS Section -->
        <div class="orders-section mt-4">

            <h4>Recent Orders Assigned to You [<?php echo $orderCount; ?>]</h4>

            <!-- Filter Section -->
            <?php if (mysqli_num_rows($barangayResult) > 0): ?>
                <div class="filter-section mb-3">
                    <form method="POST" action="" class="text-center" id="filterForm">
                        <div class="form-row align-items-center">
                            <div class="col-auto">
                                <label for="barangay" class="mr-2">Filter by Barangay:</label>
                            </div>
                            <div class="col-auto">
                                <select name="barangay" id="barangay" class="form-control w-100" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <?php
                                    while ($brgy = mysqli_fetch_assoc($barangayResult)) {
                                        $selected = ($brgy['order_barangay'] == $selected_barangay) ? 'selected' : '';
                                        echo "<option value=\"{$brgy['order_barangay']}\" $selected>{$brgy['order_barangay']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>



            <div class="row">
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Extract order information
                        $cust_name = $row['cust_name'];
                        $deliv_address = $row['deliv_address'];
                        $products = $row['products'];
                        $total_items = $row['total_items'];
                        $total_amount = $row['total_amount'];
                        $transact_status = $row['transact_status'];
                        $phone_num = $row['phone_num'];
                ?>
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-body d-flex flex-column">
                                    <div>
                                        <h5 class="card-title">Customer: <?php echo $cust_name; ?></h5>
                                        <p class="card-text">
                                            <strong>Phone Number:</strong> <?php echo $phone_num; ?><br>
                                            <strong>Delivery Address:</strong> <?php echo $deliv_address; ?><br>
                                            <strong>Total Items:</strong> <?php echo $total_items; ?><br>
                                            <strong>Total Amount:</strong> ₱<?php echo number_format($total_amount, 2); ?><br>
                                        </p>
                                    </div>
                                    <div class="mt-auto"> <!-- Pushes the footer to the bottom -->
                                        <a href="order_details.php?cust_id=<?php echo $row['cust_id']; ?>&order_barangay=<?php echo urlencode($row['order_barangay']); ?>&order_date=<?php echo urlencode($row['order_date']); ?>" class="btn btn-primary1 btn-block">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo '<div class="col text-center mt-4">';
                    echo '<i class="fas fa-exclamation-circle" style="font-size: 50px; color: #A72828;"></i>'; // Icon for no orders
                    echo '<p class="mt-3">No ongoing orders found for you at the moment.</p>';
                    echo '</div>';
                }
                ?>
            </div>

        </div>

    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Add loaded class to body after content is fully loaded
        window.onload = function() {
            document.body.classList.add('loaded');
        };

        $(document).ready(function() {
            // When the status is changed
            $('input[name="emp_status"]').change(function() {
                var newStatus = $(this).val(); // Get the selected status

                // Send the status update via AJAX
                $.ajax({
                    url: '', // Current PHP script
                    type: 'POST',
                    data: {
                        emp_status: newStatus
                    },
                    success: function(response) {
                        alert(response); // Display a success message (optional)
                    },
                    error: function() {
                        alert("Error updating status.");
                    }
                });
            });
        });

        function confirmLogout() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out of your account.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF8225',
                cancelButtonColor: '#A72828',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to the logout.php page
                    window.location.href = '../includes/logout.php';
                }
            });
        }
    </script>
</body>

</html>