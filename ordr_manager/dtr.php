<?php
// Set error reporting to ignore notices
error_reporting(E_ALL & ~E_NOTICE);

session_start();
include '../includes/db_connect.php';
include '../includes/sf_getEmpInfo.php';

if (!isset($_SESSION['emp_id'])) {
    header('Location: login.php');
    exit();
}

$emp_id = $_SESSION['emp_id'];

// Default values for month selection
$selected_month = date('Y-m'); // Current month

// Calculate the current week of the month
$current_date = new DateTime(); // Get current date
$first_day_of_month = new DateTime($selected_month . '-01'); // Get the first day of the selected month
$current_week_of_month = (int)(ceil(($current_date->format('j') + $first_day_of_month->format('N') - 1) / 7));

// If the current week exceeds the number of weeks in the month, reset to the last week
$total_days_in_month = $first_day_of_month->format('t'); // Total days in the month
$total_weeks_in_month = ceil($total_days_in_month / 7); // Total weeks in the month
$current_week_of_month = min($current_week_of_month, $total_weeks_in_month);

// Check if a month or week has been posted, and if so, redirect to avoid resubmission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['selected_month'])) {
        $selected_month = $_POST['selected_month'];
    }
    if (isset($_POST['selected_week_of_month'])) {
        $current_week_of_month = $_POST['selected_week_of_month'];
    }

    // Redirect to the same page with the selected month/week as a GET parameter
    header("Location: dtr.php?month=" . urlencode($selected_month) . "&week=" . urlencode($current_week_of_month));
    exit(); // Make sure to exit after the redirect
}

// Check if a month or week is provided in the URL
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$current_week_of_month = isset($_GET['week']) ? (int)$_GET['week'] : $current_week_of_month;

// Database connection
include '../includes/db_connect.php';

// Fetch employee details
$sql = "
    SELECT emp_fname, emp_lname, emp_role, emp_email, emp_num, emp_address 
    FROM emp_tbl 
    WHERE emp_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

// Calculate the first and last day of the selected month
$first_day_of_month = date('Y-m-01', strtotime($selected_month));
$last_day_of_month = date('Y-m-t', strtotime($selected_month));

// Fetch attendance records based on selected month and week of the month
$week_start_date = date('Y-m-d', strtotime($first_day_of_month . ' + ' . ($current_week_of_month - 1) * 7 . ' days'));
$week_end_date = date('Y-m-d', strtotime($week_start_date . ' + 6 days'));

// Ensure the week range is within the bounds of the selected month
if ($week_start_date < $first_day_of_month) {
    $week_start_date = $first_day_of_month;
}
if ($week_end_date > $last_day_of_month) {
    $week_end_date = $last_day_of_month;
}

$attendance_sql = "
    SELECT time_in, time_out, att_date 
    FROM att_tbl 
    WHERE emp_id = ? AND att_date BETWEEN ? AND ?
";
$stmt = $conn->prepare($attendance_sql);
$stmt->bind_param("sss", $emp_id, $week_start_date, $week_end_date);
$stmt->execute();
$result = $stmt->get_result();
$attendance = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();

// Calculate valid weeks for the selected month
function getValidWeeks($month)
{
    $first_day_of_month = date('Y-m-01', strtotime($month));
    $last_day_of_month = date('Y-m-t', strtotime($month));

    $weeks = [];
    $start_date = new DateTime($first_day_of_month);
    $end_date = new DateTime($last_day_of_month);

    while ($start_date <= $end_date) {
        $week_number = (int)$start_date->format('W') - (int)date('W', strtotime($first_day_of_month)) + 1;
        if (!in_array($week_number, $weeks) && $week_number > 0) {
            $weeks[] = $week_number;
        }
        $start_date->modify('+1 week');
    }

    return $weeks;
}

$valid_weeks = getValidWeeks($selected_month);

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
        .active7 {
            background: linear-gradient(180deg, #ff83259b, #a72828);
        }

        body {
            color: #fff;
            /* Default text color */
        }

        .container {
            max-width: 900px;
            margin: auto;
        }

        .company-info {
            text-align: center;
            margin-bottom: 20px;
            color: #A72828;
            /* Company Info Text Color */
        }

        .company-info img {
            max-height: 100px;
        }

        .employee-info {
            background-color: #A72828;
            /* Darker background for Employee Info */
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .employee-info h4 {
            margin-bottom: 15px;
            color: #FF8225;
            /* Highlight color for headings */
        }

        .table {
            margin-top: 20px;
            background-color: #fff;
            /* Table background */
            color: #333;
            /* Table text color */
        }

        .table th {
            background-color: #FF8225;
            /* Header color */
            color: #fff;
            /* Header text color */
        }

        .btn-secondary {
            background-color: #A72828;
            /* Back button color */
            border: none;
            /* Remove border */
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

        .btn-secondary:hover {
            background-color: #9f2525;
            /* Slightly darker on hover */
        }
        .company-info {
    text-align: center; /* Ensure everything is centered */
}

.company-info img {
    max-width: 100px; /* Adjust size of the logo */
    margin-bottom: 10px; /* Small gap between the logo and heading */
}

.company-info h3 {
    margin: 5px 0; /* Reduce vertical margin for the heading */
}

.company-info p {
    margin: 2px 0; /* Reduce vertical margin for each paragraph */
}
.employee-info p {
        margin: 5px 0; /* or use padding if preferred */
    }

    
    </style>

</head>

<body>
    <!-- Sidebar on the left -->
    <?php include '../includes/omSideBar.php'; ?>


    <!-- Main content -->
    <div class="content">

        <!-- Real-time clock -->
        <div id="clock-container" style="color: #333;">
            <div id="clock"></div>
            <div id="date"></div>
        </div>

        <hr>

        <div class="container mt-4">
        <!-- Company Information -->
        <div class="company-info">
            <img src="../img/mtdd_logo.png" alt="Company Logo"> <!-- Replace with your company logo -->
            <h3>Melo's Meatshop</h3>
            <p>Apookon RD, Tagum City, Davao del Norte</p>
            <p>Contact: +63 938 895 2457</p>
            <p>Email: meattodoor@gmail.com</p>
        </div>


        <!-- Add this section in the appropriate place in your dtr.php -->
        <form method="POST" action="download_dtr.php" class="d-flex flex-column flex-sm-row justify-content-center align-items-center my-3">
            <input type="hidden" name="emp_id" value="<?php echo $emp_id; ?>">
            <input type="hidden" name="week_start_date" value="<?php echo $week_start_date; ?>">
            <input type="hidden" name="week_end_date" value="<?php echo $week_end_date; ?>">
            <input type="hidden" name="selected_month" value="<?php echo $selected_month; ?>">

            <button type="submit" id="weekDTR" name="download_week" class="btn btn-primary mb-2 mb-sm-0 me-sm-2">
                <i class="bi bi-box-arrow-down"></i> DTR for Selected Week
            </button>
            <button type="submit" id="monthDTR" name="download_month" class="btn btn-primary">
                <i class="bi bi-box-arrow-down"></i> DTR for Whole Month
            </button>
        </form>



        <!-- Employee Information -->
        <?php if ($employee): ?>
            <div class="employee-info">
                <h4>Employee Details</h4>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($employee['emp_fname'] . ' ' . $employee['emp_lname']); ?></p>
                <p><strong>Role:</strong> <?php echo htmlspecialchars($employee['emp_role']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($employee['emp_email']); ?></p>
                <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($employee['emp_num']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($employee['emp_address']); ?></p>
            </div>

            <!-- Selection Form -->
            <form method="POST" class="mb-4">
                <label for="month" class="form-label" style="color: #A72828;">Select Month:</label>
                <input type="month" id="month" name="selected_month" class="form-control" value="<?php echo $selected_month; ?>" required onchange="this.form.submit()">
                <label for="week" class="form-label mt-3" style="color: #A72828;">Select Week of Month:</label>
                <select id="week" name="selected_week_of_month" class="form-control" required>
                    <?php foreach ($valid_weeks as $week): ?>
                        <option value="<?php echo $week; ?>" <?php echo ($week == $current_week_of_month) ? 'selected' : ''; ?>>Week <?php echo $week; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" id="viewBtn" class="btn btn-secondary mt-3" style="color: #fff; font-weight:bold;">View DTR</button>
            </form>

            <!-- Display Attendance Data -->
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance as $record): ?>
                        <tr>
                            <td>
                                <?php
                                if (is_null($record['time_in'])) {
                                    echo "None";
                                } else {
                                    echo date('h:i A', strtotime($record['time_in']));
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if (is_null($record['time_out'])) {
                                    echo "None";
                                } else {
                                    echo date('h:i A', strtotime($record['time_out']));
                                }
                                ?>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($record['att_date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>
            <p>No employee records found.</p>
        <?php endif; ?>
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


</body>

</html>