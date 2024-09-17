<?php
// Database connection
include '../includes/db_connect.php';

// Fetching the data for the shippers' transactions
$sql = "SELECT dt.transact_code, dt.shipper_id, CONCAT(e.emp_fname, ' ', e.emp_lname) AS shipper_name, dt.order_id, dt.transact_date, dt.transact_status
        FROM delivery_transactbl dt
        JOIN emp_tbl e ON dt.shipper_id = e.emp_id
        WHERE e.emp_role = 'shipper'";

$result = $conn->query($sql);

// Fetching the data for the shippers and their transactions
$month = date('m'); // Get current month
$year = date('Y');  // Get current year

$TransactSql = "SELECT e.emp_id, CONCAT(e.emp_fname, ' ', e.emp_lname) AS fullname, e.emp_img, e.emp_status,
               SUM(CASE WHEN dt.transact_status = 'Success' THEN 1 ELSE 0 END) AS success_count,
               SUM(CASE WHEN dt.transact_status = 'Failed' THEN 1 ELSE 0 END) AS failed_count
        FROM emp_tbl e
        LEFT JOIN delivery_transactbl dt ON e.emp_id = dt.shipper_id
        WHERE e.emp_role = 'shipper' AND MONTH(dt.transact_date) = ? AND YEAR(dt.transact_date) = ?
        GROUP BY e.emp_id";

$stmt = $conn->prepare($TransactSql);
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$TransactResult = $stmt->get_result();
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.6/css/dataTables.bootstrap5.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/ordr_css.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.js">

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.2/css/buttons.bootstrap5.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">


    <link rel="icon" href="../img/logo.ico" type="image/x-icon">

    <style>
        .group-start {
            border-left: 5px solid #FF8225;
            background-color: #f9f9f9;
        }

        .alternate-color {
            background-color: #eaf6f6;
        }

        .product-img {
            width: 60px;
            height: auto;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .product-img+span {
            font-size: 14px;
            line-height: 1.5;
        }

        .buttons-collection,
        .buttons-pdf,
        .buttons-copy,
        .buttons-excel,
        .dt-buttons {
            background-color: #FF8225;
            border: none;
        }

        .buttons-collection:hover,
        .buttons-pdf:hover,
        .buttons-copy:hover,
        .buttons-excel:hover,
        .dt-buttons:hover {
            background-color: #a72828;
        }

        /* Center pagination controls */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .input-group .form-control {
            height: 38px;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .input-group-text {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .input-group .form-control:focus {
            box-shadow: none;
            border-color: #FF8225;
        }

        /* Table hover effect */
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Badge Styles */
        .badge-ongoing {
            background-color: #ffc107;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-failed {
            background-color: #dc3545;
        }

        .shipper-card {
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
            height: 100%;
        }

        .shipper-card:hover {
            transform: scale(1.05);
        }

        .shipper-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .profCard {
            text-align: center;
        }

        .transaction-info {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
        }

        .success-count {
            color: #28a745;
        }

        .failed-count {
            color: #dc3545;
        }

        .shipper-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .shipper-card .card-footer {
            background-color: #f8f9fa;
            padding: 10px;
        }

        .badge-status {
            margin-top: -10px;
            /* Adjust based on image size and desired badge position */
            margin-bottom: 10px;
            font-size: 0.75rem;
            /* Adjust the font size of the badge if needed */
            padding: 0.2rem 0.3rem;
            /* Adjust padding to fit the content */
            color: #fff;
            /* Ensure the text color is readable */
            background: #28a745;
            width: max-content;
        }

        .active3 {
            background: linear-gradient(180deg, #ff83259b, #a72828);
        }
    </style>

</head>

<body>
    <!-- Sidebar on the left -->
    <?php include '../includes/omSideBar.php'; ?>



    <!-- Main content -->
    <div class="content">

        <!-- Real-time clock -->
        <div id="clock-container">
            <div id="clock"></div>
            <div id="date"></div>
        </div>

        <hr>

        <div class="container-fluid">
            <h2 class="my-4">Shippers</h2>
            <div class="shipper-container">
                <?php if ($TransactResult->num_rows > 0): ?>
                    <?php while ($row = $TransactResult->fetch_assoc()): ?>
                        <div class="card shipper-card col-md-4 col-sm-6 col-lg-3">
                            <div class="card-body profCard" style="background: url('../img/bgShip.png') no-repeat center; background-size: cover; object-fit:cover;">
                                <!-- Shipper's Profile Image -->
                                <img src="../<?php echo $row['emp_img']; ?>" alt="Shipper Image" class="shipper-img">
                                <!-- Employee Status Badge (Below Image) -->
                                <span class="badge badge-warning badge-status d-block mx-auto text-center">
                                    <?php echo $row['emp_status']; ?>
                                </span>
                                <!-- Shipper's Full Name -->
                                <h5 class="card-title"><?php echo $row['fullname']; ?></h5>

                                <!-- Monthly Transaction Summary -->
                                <div class="transaction-info">
                                    <div>
                                        <span class="success-count"><i class="fas fa-check-circle"></i> <?php echo $row['success_count']; ?></span>
                                        <p>Delivered</p>
                                    </div>
                                    <div>
                                        <span class="failed-count"><i class="fas fa-times-circle"></i> <?php echo $row['failed_count']; ?></span>
                                        <p>Failed</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer text-muted" style="background: #dc3545;">
                                <span style="color: #f8f9fa;">Monthly Report</span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No shippers found</p>
                <?php endif; ?>
            </div>
        </div>


        <hr>

        <!-- Shipper History Tracking -->
        <div class="card">
            <div class="card-header text-dark" style="background: #a72828;">
                <h3 class="mb-0" style="color: #ffffff;">Shippers/<span style="color: #c2c2c2; font-size:20px; font-weight:500;">History Transaction Tracking</span></h3>
            </div>

            <!-- Date Picker with icon on the right side -->
            <div class="container mb-1" style="margin-top: 20px;">
                <div class="row">
                    <div class="col-md-12">
                        <div class="input-group date float-end" style="width: 250px;">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-calendar-alt" style="color: #a72828;"></i>
                            </span>
                            <input type="text" id="transactDatePicker" class="form-control datepicker" placeholder="Select Transaction Date" readonly>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Status Filter Dropdown -->
            <div class="container mb-1" style="margin-top: 0px;">
                <div class="row">
                    <div class="col-md-12">
                        <div class="input-group float-end" style="width: 250px;">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-filter" style="color: #a72828;"></i>
                            </span>
                            <select id="statusFilter" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="Ongoing">Ongoing</option>
                                <option value="Success">Success</option>
                                <option value="Failed">Failed</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>



            <div class="card-body">
                <div class="table-responsive overflow-auto">
                    <table class="table table-striped table-hover" id="shippersTable">
                        <thead>
                            <tr>
                                <th>Transaction Code</th>
                                <th>Shipper ID</th>
                                <th>Shipper Name</th>
                                <th>Order ID</th>
                                <th>Transaction Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['transact_code']; ?></td>
                                        <td><?php echo $row['shipper_id']; ?></td>
                                        <td><?php echo $row['shipper_name']; ?></td>
                                        <td><?php echo $row['order_id']; ?></td>
                                        <td><?php echo $row['transact_date']; ?></td>
                                        <td>
                                            <?php if ($row['transact_status'] == 'Ongoing'): ?>
                                                <span class="badge badge-ongoing">Ongoing</span>
                                            <?php elseif ($row['transact_status'] == 'Success'): ?>
                                                <span class="badge badge-success">Success</span>
                                            <?php else: ?>
                                                <span class="badge badge-failed">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No records found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> <!--End of Container-->

    <!-- DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.6/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.6/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.bootstrap5.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <script src="../js/order_manager.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTables with pagination, and set page length to 10
            var table = $('#shippersTable').DataTable({
                "pageLength": 10,
                "ordering": true,
                "autoWidth": true,
                "responsive": true,
                "order": [
                    [4, 'desc']
                ],
                dom: 'Bfrtip', // Ensure buttons are displayed
                buttons: [{
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i> Copy',
                        titleAttr: 'Copy'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        titleAttr: 'Excel'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        titleAttr: 'PDF'
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i> Columns',
                        titleAttr: 'Column Visibility'
                    }
                ]
            });

            // Initialize Date Picker
            $('#transactDatePicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });

            // Filter DataTable on Date Picker change
            $('#transactDatePicker').on('changeDate', function() {
                var selectedDate = $(this).val();
                table.columns(4).search(selectedDate).draw(); // 4 is the index for the 'Date' column
            });

            // Filter DataTable on Status Dropdown change
            $('#statusFilter').on('change', function() {
                var selectedStatus = $(this).val();
                table.columns(5).search(selectedStatus).draw(); // 5 is the index for the 'Status' column
            });


        });
    </script>

</body>

</html>