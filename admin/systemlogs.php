<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | System Logs</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.6/css/dataTables.bootstrap5.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/ordr_css.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="icon" href="../img/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/admin.css">

    <style>
        /* Custom styles for the table */
        .table th,
        .table td {
            vertical-align: middle;
            /* Center align the content */
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            /* Light gray background on hover */
        }

        .table-wrapper {
            margin-top: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .loading {
            display: none;
            /* Initially hide the loader */
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
        }
    </style>
</head>

<body>
    <div class="d-flex" id="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <div class="content-header">
                    <h3 class="mt-4" id="dashboard">System Logs</h3>
                </div>

                <div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading logs...</div>

                <div class="table-wrapper">
                    <div class="table-responsive overflow-auto">
                        <table id="logsTable" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User ID</th>
                                    <th>User Name</th>
                                    <th>User Type</th>
                                    <th>Action</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Logs will be populated here via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Employee Logs Section -->
                <div class="content-header mt-5">
                    <h3 id="employee-logs">Orders Transaction Logs</h3>
                </div>
                <div class="table-wrapper">
                    <div class="table-responsive overflow-auto">
                        <table id="employeeLogsTable" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Log ID</th>
                                    <th>Employee ID</th>
                                    <th>Order ID</th>
                                    <th>Action</th>
                                    <th>Employee Name</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div> <!-- /#page-content-wrapper -->
    </div> <!-- End of wrapper -->

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.6/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.6/js/dataTables.bootstrap5.js"></script>

    <script>
        $(document).ready(function() {
            // Show loading spinner
            $('.loading').show();

            // Fetch logs from the server
            $.ajax({
                url: 'fetch_logs.php', // Update this to the correct path
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    var tableBody = $('#logsTable tbody');

                    // Clear existing data if any
                    tableBody.empty();

                    if (data.length === 0) {
                        tableBody.append('<tr><td colspan="6">No logs found.</td></tr>');
                    } else {
                        // Populate the DataTable with logs
                        data.forEach(function(log) {
                            var date = new Date(log.systemlog_date);

                            // Format the date into 12-hour format with AM/PM
                            var formattedDate = date.toLocaleString('en-US', {
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit',
                                hour12: true, // 12-hour format
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });

                            var row = '<tr>' +
                                '<td>' + log.id + '</td>' +
                                '<td>' + log.user_id + '</td>' +
                                '<td>' + log.user_name + '</td>' +
                                '<td>' + log.user_type + '</td>' +
                                '<td>' + log.systemlog_action + '</td>' +
                                '<td>' + formattedDate + '</td>' +
                                '</tr>';
                            tableBody.append(row);
                        });
                    }

                    // Check if DataTable is already initialized
                    if ($.fn.DataTable.isDataTable('#logsTable')) {
                        $('#logsTable').DataTable().clear().destroy();
                    }

                    // Initialize DataTable with descending order
                    $('#logsTable').DataTable({
                        order: [
                            [0, 'desc']
                        ], // Sort by the first column (index 0) in descending order
                    });

                    // Hide loading spinner
                    $('.loading').hide();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Error fetching logs: " + textStatus, errorThrown);
                    alert("Failed to fetch logs. Please try again later.");
                    $('.loading').hide();
                }
            });
        });

        $.ajax({
            url: 'fetch_employee_logs.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                var tableBody = $('#employeeLogsTable tbody');

                // Clear any existing rows to avoid duplication
                tableBody.empty();

                // Append logs to the table
                data.forEach(function(log) {
                    var row = '<tr>' +
                        '<td>' + log.emplog_id + '</td>' +
                        '<td>' + log.emp_id + '</td>' +
                        '<td>' + log.order_id + '</td>' +
                        '<td>' + log.emplog_action + '</td>' +
                        '<td>' + log.employee_name + '</td>' +
                        '<td>' + new Date(log.emplog_date).toLocaleString() + '</td>' +
                        '</tr>';
                    tableBody.append(row);
                });

                // Initialize DataTable and set default sorting
                $('#employeeLogsTable').DataTable({
                    order: [
                        [0, 'desc']
                    ] // Sort by the first column (emplog_id) in descending order
                });

                // Hide loading spinner
                $('.loading').hide();
            },
            error: function() {
                console.error("Error fetching employee logs.");
                $('.loading').hide();
            }
        });
    </script>
</body>

</html>