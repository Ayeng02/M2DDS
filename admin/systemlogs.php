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
        .table th, .table td {
            vertical-align: middle; /* Center align the content */
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa; /* Light gray background on hover */
        }

        .table-wrapper {
            margin-top: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .loading {
            display: none; /* Initially hide the loader */
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
        $(document).ready(function () {
            // Show loading spinner
            $('.loading').show();

            // Fetch logs from the server
            $.ajax({
                url: 'fetch_logs.php', // Update this to the correct path
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    // Populate the DataTable with logs
                    var tableBody = $('#logsTable tbody');
                    data.forEach(function (log) {
                        var row = '<tr>' +
                            '<td>' + log.id + '</td>' +
                            '<td>' + log.user_id + '</td>' +
                            '<td>' + log.user_name + '</td>' +
                            '<td>' + log.user_type + '</td>' +
                            '<td>' + log.systemlog_action + '</td>' +
                            '<td>' + new Date(log.systemlog_date).toLocaleString() + '</td>' +
                            '</tr>';
                        tableBody.append(row);
                    });
                    $('#logsTable').DataTable(); // Initialize DataTable
                    $('.loading').hide(); // Hide loading spinner after data is loaded
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error("Error fetching logs: " + textStatus, errorThrown);
                    $('.loading').hide(); // Hide loading spinner on error
                }
            });
        });

        $.ajax({
    url: 'fetch_employee_logs.php', 
    method: 'GET',
    dataType: 'json',
    success: function (data) {
        var tableBody = $('#employeeLogsTable tbody');
        data.forEach(function (log) {
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
        $('#employeeLogsTable').DataTable();
        $('.loading').hide();
    },
    error: function () {
        console.error("Error fetching employee logs.");
        $('.loading').hide();
    }
});

    </script>
</body>

</html>
