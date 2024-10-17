<?php
include '../includes/sf_getEmpInfo.php'; // Existing employee info retrieval

$shipper_id = $_SESSION['emp_id'];

// Query to get all transactions related to the shipper, grouped by cust_id, order_barangay, and order_date
$transactionsQuery = "
    SELECT 
        o.cust_id,
        CONCAT(c.f_name, ' ', c.l_name) AS cust_name,
        CONCAT(o.order_purok, ' ', o.order_barangay, ' ', o.order_province) AS deliv_address,
        d.transact_date,
        o.status_code,
        d.transact_status,
        b.Brgy_name,
        o.order_barangay,
        b.Brgy_df,
        SUM(o.order_total) + b.Brgy_df AS total_amount,
        COUNT(o.order_id) AS total_items, -- Counting total items in the group
        o.order_id  -- Fetching order_id for details
    FROM 
        delivery_transactbl d
    JOIN 
        order_tbl o ON d.order_id = o.order_id
    JOIN 
        customers c ON o.cust_id = c.cust_id
    JOIN 
        brgy_tbl b ON o.order_barangay = b.Brgy_name
    WHERE 
        d.shipper_id = '$shipper_id'
    GROUP BY 
        o.cust_id, o.order_barangay, o.order_date, d.transact_status, d.transact_date
    ORDER BY 
        d.transact_date DESC;
";

$result = mysqli_query($conn, $transactionsQuery);
$transactionCount = mysqli_num_rows($result); // Count the number of transactions

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
    <title>Shipper Transactions</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <style>
        .dataTables_length,
        .dataTables_filter {
            margin-top: 10px;
        }

        .dataTables_filter {
            margin-right: 5px;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .modal-title {
            color: #A72828;
            /* Adjust title color to match theme */
        }
        .btn-primary{
            background-color: #FF8225;
            /* Back button color */
            border: none;
            /* Remove border */
        }
        .btn-primary:hover{
            background-color: #e5833b;
        }
        .profile-card {
    background-color: #A72828;
    color: white;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.profile-card .profile-circle {
    width: 60px;
    height: 60px;
    background-color: #FF8225;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}
    </style>
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

        <!-- Heading Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 style="color: #A72828; font-weight:500;"> <i class="fas fa-clock-rotate-left"></i> Transaction History</h3>
        </div>

        <!-- Back Button -->
        <div class="mb-3">
            <a href="shipper.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>


        <!-- Transaction Table -->
        <div class="table-responsive" style="margin-top: 10px;">
            <table id="transactionsTable" class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Customer</th>
                        <th>Delivery Address</th>
                        <th>Transaction Date</th>
                        <th>Transaction Status</th>
                        <th>Total Amount</th>
                        <th>Total Items</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($transactionCount > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['cust_name']; ?></td>
                                <td><?php echo $row['deliv_address']; ?></td>
                                <td><?php echo date('F j, Y', strtotime($row['transact_date'])); ?></td>
                                <td><?php echo $row['transact_status']; ?></td>
                                <td>₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                <td><?php echo $row['total_items']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info view-details"
                                        data-cust-id="<?php echo $row['cust_id']; ?>"
                                        data-barangay="<?php echo $row['order_barangay']; ?>"
                                        data-transact-date="<?php echo $row['transact_date']; ?>">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Transaction Details -->
    <div class="modal fade" id="transactionModal" tabindex="-1" role="dialog" aria-labelledby="transactionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transactionModalLabel"> <i class="fas fa-circle-info"></i> Transaction Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Transaction details will be loaded here via AJAX -->
                    <div id="transactionDetailsContent">
                        <p>Loading details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
                // Add loaded class to body after content is fully loaded
                window.onload = function() {
            document.body.classList.add('loaded');
        };
        $(document).ready(function() {
    $('#transactionsTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search transactions..."
        }
    });

    // Dynamically bind the event handler using $(document).on()
    $(document).on('click', '.view-details', function() {
        var custId = $(this).data('cust-id');
        var barangay = $(this).data('barangay');
        var transactDate = $(this).data('transact-date');

        // AJAX call to fetch all orders matching the cust_id, order_barangay, and transact_date
        $.ajax({
            url: 'fetch_transaction_details.php', // Backend file to retrieve transaction details
            type: 'GET',
            data: {
                cust_id: custId,
                order_barangay: barangay,
                transact_date: transactDate
            },
            success: function(response) {
                // Populate modal with transaction details
                $('#transactionDetailsContent').html(response); // Assuming response is HTML
                $('#transactionModal').modal('show');
            },
            error: function() {
                $('#transactionDetailsContent').html('<p>Error loading details. Please try again later.</p>');
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