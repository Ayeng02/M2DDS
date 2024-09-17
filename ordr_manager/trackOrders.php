<?php
// Database connection
include '../includes/db_connect.php';

// SQL query to fetch order data including product image
$sql = "SELECT * FROM order_summary_view";
$result = $conn->query($sql);
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

        .active2 {
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

        <!-- Order List Card -->
        <div class="card">
            <div class="card-header text-dark" style="background: #a72828;">
                <h3 class="mb-0" style="color: #ffffff;">Order Lists/<span style="color: #c2c2c2; font-size:20px; font-weight:500;">History</span></h3>
            </div>

            <!-- Date Picker with icon on the right side -->
            <div class="container mb-1" style="margin-top: 20px;">
                <div class="row">
                    <div class="col-md-12">
                        <div class="input-group date float-end" style="width: 250px;">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-calendar-alt" style="color: #a72828;"></i>
                            </span>
                            <input type="text" id="orderDatePicker" class="form-control datepicker" placeholder="Select Order Date" readonly>
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
                                <option value="Pending">Pending</option>
                                <option value="Processing">Processing</option>
                                <option value="Shipped">Shipped</option>
                                <option value="Delivered">Delivered</option>
                                <option value="Canceled">Canceled</option>
                                <option value="Failed">Failed</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>


            <div class="card-body">
                <div class="table-responsive overflow-auto">
                    <table class="table table-striped" id="allOrdersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Product</th>
                                <th>Full Name</th>
                                <th>Address</th>
                                <th>Quantity (Kg.)</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $previousCustId = null;
                            $previousOrderDate = null;
                            $highlightClass = '';

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    if ($row['cust_id'] !== $previousCustId || $row['formatted_order_date'] !== $previousOrderDate) {
                                        $highlightClass = 'group-highlight';
                                    } else {
                                        $highlightClass = '';
                                    }

                                    $badgeClass = '';
                                    switch ($row['status_name']) {
                                        case 'Pending':
                                            $badgeClass = 'bg-warning text-dark';
                                            break;
                                        case 'Processing':
                                            $badgeClass = 'bg-info text-white';
                                            break;
                                        case 'Shipped':
                                            $badgeClass = 'bg-primary text-white';
                                            break;
                                        case 'Delivered':
                                            $badgeClass = 'bg-success text-white';
                                            break;
                                        case 'Canceled':
                                            $badgeClass = 'bg-danger text-white';
                                            break;
                                        case 'Failed':
                                            $badgeClass = 'bg-secondary text-white';
                                            break;
                                        default:
                                            $badgeClass = 'bg-light text-dark';
                                    }

                                    echo "<tr class='{$highlightClass}'>
                                            <td>{$row['order_id']}</td>
                                            <td class='text-center'>
                                                <div class='d-flex align-items-center'>
                                                    <img src='../{$row['prod_img']}' alt='{$row['prod_name']}' class='product-img' />
                                                    <span class='ms-2'>{$row['prod_name']}</span>
                                                </div>
                                            </td>
                                            <td>{$row['order_fullname']}</td>
                                            <td>{$row['address']}</td>
                                            <td>{$row['order_qty']}</td>
                                            <td>{$row['total_with_brgy_df']}</td>
                                            <td>{$row['formatted_order_date']}</td>

                                            <td class='text-center'><span class='badge {$badgeClass}'>{$row['status_name']}</span></td>
                                          </tr>";

                                    $previousCustId = $row['cust_id'];
                                    $previousOrderDate = $row['formatted_order_date'];
                                }
                            } else {
                                echo "<tr><td colspan='8'>No orders found</td></tr>";
                            }

                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div> <!-- End of Order List Card -->
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
            var table = $('#allOrdersTable').DataTable({
                "pageLength": 10,
                "ordering": true,
                "autoWidth": true,
                "responsive": true,
                "order": [
                    [6, 'desc']
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
            $('#orderDatePicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });

            // Filter DataTable on Date Picker change
            $('#orderDatePicker').on('changeDate', function() {
                var selectedDate = $(this).val();
                table.columns(6).search(selectedDate).draw(); // 6 is the index for the 'Date' column
            });

            // Filter DataTable on Status Dropdown change
            $('#statusFilter').on('change', function() {
                var selectedStatus = $(this).val();
                table.columns(7).search(selectedStatus).draw(); // 7 is the index for the 'Status' column
            });
        });
    </script>

</body>

</html>