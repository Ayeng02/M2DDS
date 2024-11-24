<?php
ob_start();
session_start();
include '../includes/db_connect.php';


// Redirect to landing page if already logged in
if (isset($_SESSION['EmpLogExist']) && $_SESSION['EmpLogExist'] === true || isset($_SESSION['AdminLogExist']) && $_SESSION['AdminLogExist'] === true) {


    if (isset($_SESSION['emp_role'])) {
        // Redirect based on employee role
        switch ($_SESSION['emp_role']) {
            case 'Shipper':
                header("Location: ../shipper/shipper.php");
                exit;
            case 'Order Manager':
                header("Location: ../ordr_manager/order_manager.php");
                exit;
            case 'Cashier':
                header("Location: ../cashier/cashier.php");
                exit;
                break;
            default:
        }
    }
} else {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In/Out Config</title>
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>


    <style>
        body {
            overflow-x: hidden;
            background-color: #f8f9fa;
        }


        #sidebar-wrapper .sidebar-heading .sidebar-title {
            font-size: 1.5rem;
            display: inline;
        }

        #wrapper {
            display: flex;
            width: 100%;
            height: 100%;
            /* Full viewport height */
        }

        #sidebar-wrapper {
            min-height: 100vh;
            width: 80px;
            /* Default width for icons only */
            background-color: #a72828;
            color: #fff;
            transition: width 0.3s ease;
            overflow-y: auto;
            /* Allow vertical scrolling */
            position: relative;
            overflow-x: hidden;
            /* Prevent horizontal scrolling */
            border-right: 1px solid #ddd;
            /* Light border to separate from content */
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            /* Subtle shadow */

        }

        #sidebar-wrapper.expanded {
            width: 250px;
            /* Expanded width */
        }

        #sidebar-wrapper .sidebar-heading {
            padding: 1rem;
            display: flex;
            align-items: center;
            background-color: #FF8225;
            color: #fff;
            border-bottom: 1px solid #ddd;
            /* Border for separation */
        }

        #sidebar-wrapper .logo-img {
            width: 40px;
            /* Adjust size as needed */
            height: 40px;
            margin-right: 10px;
            /* Space between logo and text */
        }

        #sidebar-wrapper .sidebar-title {
            font-size: 1.5rem;
            display: inline;
            /* Ensure title is always visible */
        }

        #sidebar-wrapper .list-group {
            width: 100%;
        }

        #sidebar-wrapper .list-group-item {
            background-color: #a72828;
            color: #fff;
            border: none;
            padding: 1rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            border-radius: 0;
            /* Remove default border radius */
            transition: background-color 0.2s ease;
            /* Smooth hover effect */
        }

        #sidebar-wrapper .list-group-item i {
            font-size: 1.5rem;
            margin-right: 15px;
        }

        #sidebar-wrapper .list-group-item span {
            display: none;
            /* Hide text in default state */
            margin-left: 10px;
            white-space: nowrap;
            /* Prevent text wrapping */
        }

        #sidebar-wrapper.expanded .list-group-item span {
            display: inline;
            /* Show text in expanded state */
        }

        #sidebar-wrapper .list-group-item:hover {
            background-color: #8c1c1c;
            /* Darker color on hover */
        }

        #sidebar-wrapper .toggle-btn {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #FF8225;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
            /* Button shadow */
        }

        #sidebar-wrapper .toggle-btn:hover {
            background-color: #a72828;
        }

        #page-content-wrapper {
            flex: 1;
            padding: 20px;
            transition: margin-left 0.3s ease;
            background-color: #f8f9fa;
            /* Slightly different background */
        }

        #page-content-wrapper.sidebar-expanded {
            margin-left: 0px;
            /* Match the expanded sidebar width */
        }

        .navbar-light {
            background-color: #FF8225;
        }

        .navbar-light .navbar-nav .nav-link {
            color: black;


        }

        .navbar-light .navbar-nav .nav-link:hover {
            color: #a72828;
        }

        /* Hide sidebar heading text when collapsed */
        #sidebar-wrapper:not(.expanded) .sidebar-title {
            display: none;
        }

        #sidebar-wrapper:not(.expanded) .logo-img {
            width: 30px;
            /* Adjust size when collapsed */
            height: 30px;
        }

        .employee-table-container {
            display: block;
            flex-direction: row;
            width: 95%;
            height: 50vh;
            margin-top: 4%;
            margin-left: 2%;
            padding-top: 20px;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            flex-direction: column;
            text-align: center;

        }

        .tbod tr{
            font-size: 20px;
        }


        #header-table-title {
            text-align: start;
            margin-bottom: 0.1rem;
            margin-left: 1%;
            font-size: 45px;
            font-weight: bold;
            color: #8c1c1c;

        }

        .combo-box {
            display: flex;
            margin-left: 30px;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 8px;
            margin-right: 20px;
        }

        .combo-box button:hover {
            background-color: #0062cd;
        }


        .edit-icon i {
            color: #007bff;
            font-size: 16px;
            cursor: pointer;
            margin-right: 10px;
            transition: color 0.2s ease;
        }


        .edit-icon i:hover {
            color: #0056b3;
        }

        .custom-font-size {
            font-size: 17px;

        }

        #editRole {
            cursor: none;
        }



        #copyTableBtn {
            margin-left: 2%;
            color: white;
        }

        .table-header {
            background-color: #8c1c1c;
        }

        nav {
            margin-top: 1%;
        }

        .navbar {
            background-color: white;
            margin-left: 3rem;
        }

        .realTime-container {
            text-align: center;
            padding: 1px;
            width: 95%;
            margin-left: 2%;
        }

        .clocktitle h3 {
            font-size: 25px;
            font-weight: bold;
            color: #ff8225
        }

        .timeclockcontainer h1 {
            font-size: 27px;
            color: black;
            margin: 10px 0;
        }

        .timeclockcontainer h5 {
            font-size: 18px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="d-flex" id="wrapper">
        <?php
        include '../includes/sidebar.php';
        ?>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <?php
            include '../includes/admin-navbar.php';
            ?>
            <?php
            if (isset($_POST['save'])) {
                $morningLoginStart = $_POST['startLogin'];
                $morningLoginEnd = $_POST['endLogin'];
                $afternoonLogoutStart = $_POST['startLogout'];
                $afternoonLogoutEnd = $_POST['endLogout'];

                // Check if the attendsched_tbl already has a schedule
                $checkQuery = "SELECT COUNT(*) AS schedule_count FROM attendsched_tbl";
                $result = $conn->query($checkQuery);
                $row = $result->fetch_assoc();

                if ($row['schedule_count'] > 0) {
                    // Schedule data already exists, show an alert message
                    $_SESSION['alert'] = [
                        'icon' => 'warning',
                        'title' => 'Schedule already exists. Update it instead of adding a new one.'
                    ];
                } else {
                    // Prepare the SQL query to insert data into the attendsched_tbl table
                    $sql = "INSERT INTO attendsched_tbl (am_login_start, am_login_end, pm_logout_start, pm_logout_end) VALUES (?, ?, ?, ?)";

                    if ($stmt = $conn->prepare($sql)) {
                        // Bind parameters to the query
                        $stmt->bind_param("ssss", $morningLoginStart, $morningLoginEnd, $afternoonLogoutStart, $afternoonLogoutEnd);

                        // Execute the query to insert the new schedule
                        if ($stmt->execute()) {
                            // Successfully inserted
                            $_SESSION['alert'] = [
                                'icon' => 'success',
                                'title' => 'Attendance schedule added successfully.'
                            ];
                        } else {
                            // Error executing the insert query
                            echo "Error: " . $stmt->error;
                        }

                        // Close the statement
                        $stmt->close();
                    } else {
                        echo "Error preparing statement: " . $conn->error;
                    }
                }

                // Redirect to prevent form resubmission and show the alert message
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }

            // Check for alerts after the form processing
            if (isset($_SESSION['alert'])) {
                $alert = $_SESSION['alert'];
                echo '<script>
                    Swal.fire({
                        icon: "' . $alert['icon'] . '",
                        title: "' . $alert['title'] . '",
                        showConfirmButton: true
                    });
                  </script>';
                unset($_SESSION['alert']); // Clear the alert so it doesn't show again
            }
            ?>
            <!-- Set Schedule -->
            <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="editModalLabel">Set Employee Attendance Schedule</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="row g-3" action="" method="post" enctype="multipart/form-data">
                                <!-- Schedule Inputs -->
                                <h6 class="mt-4 fw-bold">Morning Schedule</h6>
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <label for="morningLoginStart" class="form-label fw-bold custom-font-size">Login Start</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="time" name="startLogin" class="form-control custom-font-size" id="morningLoginStart" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <label for="morningLoginEnd" class="form-label fw-bold custom-font-size">Login End</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="time" name="endLogin" class="form-control custom-font-size" id="morningLoginEnd" required>
                                    </div>
                                </div>

                                <h6 class="mt-4 fw-bold">Afternoon Schedule</h6>
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <label for="afternoonLogoutStart" class="form-label fw-bold custom-font-size">Logout Start</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="time" name="startLogout" class="form-control custom-font-size" id="afternoonLogoutStart" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <label for="afternoonLogoutEnd" class="form-label fw-bold custom-font-size">Logout End</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="time" name="endLogout" class="form-control custom-font-size" id="afternoonLogoutEnd" required>
                                    </div>
                                </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" name="save">Save Schedule</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>



            <div id="header-table-title">Manage Attendance Schedule</div>
            <div class="employee-table-container">
                <div class="d-grid gap-10 col-14 ms-auto">
                    <div class="realTime-container">

                        <div class="clocktitle">
                            <h3>CURRENT TIME AND DATE</h3>
                        </div>
                        <div class="timeclockcontainer">
                            <h1 id="current-time">--:--:--</h1>
                            <h5 id="current-date">Loading...</h5>
                        </div>
                        <div class="clockfoot"></div>
                    </div>
                </div>
                <div class="combo-box">
                    <button type="submit" name="edit" class="btn btn-primary w-70 " data-bs-toggle="modal" data-bs-target="#attendanceModal"><i class="fa-sharp fa-solid fa-plus"></i> Set Schedule</button>
                </div>
                <?php

                $sql = "SELECT 
                       am_login_start, am_login_end, pm_logout_start, pm_logout_end
                        FROM 
                            attendsched_tbl
                        GROUP BY 
                            am_login_start, am_login_end, pm_logout_start, pm_logout_end";

                $result = $conn->query($sql);
                ?>


                <div class="table-responsive">
                    <table class="table table-hover" id="payrollTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Login Start (Morning)</th>
                                <th>Login End (Morning)</th>
                                <th>Logout Start (Afternoon)</th>
                                <th>Logout End (Afternoon)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="tbod">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($sched = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td> <?php
                                                $loginStart = new DateTime($sched['am_login_start']);
                                                echo $loginStart->format('g:i A');
                                                ?></td>
                                        <td> <?php
                                                $loginEnd = new DateTime($sched['am_login_end']);
                                                echo $loginEnd->format('g:i A');
                                                ?></td>
                                        <td>
                                            <?php
                                            $logoutStart = new DateTime($sched['pm_logout_start']);
                                            echo $logoutStart->format('g:i A');
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $logoutEnd = new DateTime($sched['pm_logout_end']);
                                            echo $logoutEnd->format('g:i A');
                                            ?>
                                        </td>

                                        <td>
                                            <!-- Edit button trigger modal -->
                                            <a href="#" class="edit-icon" data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-logstart-AM="<?php echo htmlspecialchars($sched['am_login_start']); ?>"
                                                data-logend-AM="<?php echo htmlspecialchars($sched['am_login_end']); ?>"
                                                data-logstart-PM="<?php echo htmlspecialchars($sched['pm_logout_start']); ?>"
                                                data-logend-PM="<?php echo htmlspecialchars($sched['pm_logout_end']); ?>">

                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No Schedule found.</td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>

    <?php
    if (isset($_POST['edit'])) {
        $morningLoginStart = $_POST['startLogin'];
        $morningLoginEnd = $_POST['endLogin'];
        $afternoonLogoutStart = $_POST['startLogout'];
        $afternoonLogoutEnd = $_POST['endLogout'];

        // Check if a schedule already exists in the attendsched_tbl table
        $checkQuery = "SELECT COUNT(*) AS schedule_count FROM attendsched_tbl";

        $result = $conn->query($checkQuery);
        $row = $result->fetch_assoc();

        if ($row['schedule_count'] > 0) {
            // Schedule already exists, update the existing schedule
            $updateQuery = "UPDATE attendsched_tbl 
                        SET am_login_start = ?, am_login_end = ?, pm_logout_start = ?, pm_logout_end = ?";

            if ($stmt = $conn->prepare($updateQuery)) {
                // Bind the parameters to the prepared statement
                $stmt->bind_param("ssss", $morningLoginStart, $morningLoginEnd, $afternoonLogoutStart, $afternoonLogoutEnd);

                // Execute the update query
                if ($stmt->execute()) {
                    // Successfully updated the schedule
                    $_SESSION['alert'] = [
                        'icon' => 'success',
                        'title' => 'Attendance schedule updated successfully.'
                    ];
                } else {
                    // Error executing the update query
                    $_SESSION['alert'] = [
                        'icon' => 'error',
                        'title' => 'Failed to update attendance schedule. Please try again.'
                    ];
                }
                $stmt->close();
            } else {
                // Error preparing the update statement
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error preparing update statement: ' . $conn->error
                ];
            }
        } else {
            // Schedule does not exist, show a message
            $_SESSION['alert'] = [
                'icon' => 'warning',
                'title' => 'No schedule found. Please add a schedule first.'
            ];
        }

        // Redirect back to the page to prevent form resubmission and show the alert message
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Check for alerts after the form processing
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo '<script>
    Swal.fire({
        icon: "' . $alert['icon'] . '",
        title: "' . $alert['title'] . '",
        showConfirmButton: true
    });
    </script>';
        unset($_SESSION['alert']); // Clear the alert so it doesn't show again
    }

?>

            <!-- Edit Employee Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="editModalLabel"> Edit Attendance Schedule</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="editEmployeeForm" action="" method="POST">
                            <div class="modal-body">
                                <!-- Schedule Inputs -->
                                <h6 class="mt-4 fw-bold">Morning Schedule</h6>
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <label for="morningLoginStart" class="form-label fw-bold custom-font-size">Login Start</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="time" name="startLogin" class="form-control custom-font-size" id="morningLoginStart" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <label for="morningLoginEnd" class="form-label fw-bold custom-font-size">Login End</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="time" name="endLogin" class="form-control custom-font-size" id="morningLoginEnd" required>
                                    </div>
                                </div>

                                <h6 class="mt-4 fw-bold">Afternoon Schedule</h6>
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <label for="afternoonLogoutStart" class="form-label fw-bold custom-font-size">Logout Start</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="time" name="startLogout" class="form-control custom-font-size" id="afternoonLogoutStart" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <label for="afternoonLogoutEnd" class="form-label fw-bold custom-font-size">Logout End</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="time" name="endLogout" class="form-control custom-font-size" id="afternoonLogoutEnd" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="edit" class="btn btn-primary">Save changes</button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>







            <!-- Bootstrap and JavaScript -->
            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
            <script src="https://cdn.datatables.net/2.1.6/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/2.1.6/js/dataTables.bootstrap4.min.js"></script>



            <script>
                // Toggle sidebar
                $("#menu-toggle, #menu-toggle-top").click(function(e) {
                    e.preventDefault();
                    $("#sidebar-wrapper").toggleClass("expanded");
                    $("#page-content-wrapper").toggleClass("sidebar-expanded");
                    // Change icon on toggle
                    let icon = $("#sidebar-wrapper .toggle-btn i");
                    if ($("#sidebar-wrapper").hasClass("expanded")) {
                        icon.removeClass("fa-chevron-right").addClass("fa-chevron-left");
                    } else {
                        icon.removeClass("fa-chevron-left").addClass("fa-chevron-right");
                    }
                });


                $('#editModal').on('show.bs.modal', function(event) {
                    // Get the clicked edit icon
                    var button = $(event.relatedTarget);

                    // Get data attributes from the clicked edit icon
                    var morningLoginStart = button.data('logstart-am');
                    var morningLoginEnd = button.data('logend-am');
                    var afternoonLogoutStart = button.data('logstart-pm');
                    var afternoonLogoutEnd = button.data('logend-pm');

                    // Populate the modal form fields with the retrieved data
                    var modal = $(this);
                    modal.find('#morningLoginStart').val(morningLoginStart);
                    modal.find('#morningLoginEnd').val(morningLoginEnd);
                    modal.find('#afternoonLogoutStart').val(afternoonLogoutStart);
                    modal.find('#afternoonLogoutEnd').val(afternoonLogoutEnd);
                });


                function applyDefaultMeridian() {
                    const morningLoginStart = document.getElementById("morningLoginStart");
                    const morningLoginEnd = document.getElementById("morningLoginEnd");
                    const afternoonLogoutStart = document.getElementById("afternoonLogoutStart");
                    const afternoonLogoutEnd = document.getElementById("afternoonLogoutEnd");

                    function setMeridian(field, meridian) {
                        if (field.value) {
                            let [hours, minutes] = field.value.split(':');
                            hours = parseInt(hours);
                            if (meridian === 'AM' && hours >= 12) hours -= 12;
                            if (meridian === 'PM' && hours < 12) hours += 12;
                            field.value = `${hours.toString().padStart(2, '0')}:${minutes}`;
                        }
                    }

                    morningLoginStart.addEventListener("blur", () => setMeridian(morningLoginStart, 'AM'));
                    morningLoginEnd.addEventListener("blur", () => setMeridian(morningLoginEnd, 'AM'));
                    afternoonLogoutStart.addEventListener("blur", () => setMeridian(afternoonLogoutStart, 'PM'));
                    afternoonLogoutEnd.addEventListener("blur", () => setMeridian(afternoonLogoutEnd, 'PM'));
                }

                // Run the meridian application when modal is opened
                document.getElementById("attendanceModal").addEventListener("shown.bs.modal", applyDefaultMeridian);


                function updateTime() {
                    var xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            var response = JSON.parse(xhr.responseText);
                            document.getElementById("current-time").innerHTML = response.time;
                            document.getElementById("current-date").innerHTML = response.date;
                        }
                    };
                    xhr.open("GET", "get_time.php", true);
                    xhr.send();
                }

                // Call updateTime initially and then every second
                updateTime();
                setInterval(updateTime, 1000);
            </script>

</body>

</html>