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
    <title>Attempt Logs</title>
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
            display: flex;
            flex-direction: row;
            width: 95%;
            height: 55vh;
            margin-top: 10px;
            margin-left: 2%;
            padding-top: 20px;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            flex-direction: column;
            text-align: center;

        }

        #header-table-title {
            text-align: start;
            margin-bottom: 1%;
            margin-left: 1%;
            font-size: 45px;
            font-weight: 700;
            color: #8c1c1c;
        }

        .combo-box {
            display: flex;

            margin-left: 30px;

            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 5px;
            margin-right: 10px;
        }

        .combo-box label,
        .combo-box select {
            font-size: 16px;
            margin-left: 2px;
        }

        .combo-box label {
            font-weight: bold;
            margin-right: 10px;
        }

        .combo-box select {
            padding: 5px 10px;
            border-radius: 5px;
            border: 1px solid #007bff;
            font-size: 16px;
            color: #007bff;
            background-color: white;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: border-color 0.3s ease;
        }

        .combo-box select:focus {
            outline: none;
            border-color: #a72828;
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

        .form-control[type="search"] {
            border: 1px solid;
            border-color: #007bff;
            max-width: 400px;

        }

        #add {
            margin-top: 1%;
        }

        #search {
            background-color: #007bff;
            color: white;
            border: none;
            width: 7rem;
            font-weight: bold;
        }

        #search:hover {
            background-color: #0056b3;
            /* Darker blue background on hover */
            color: white;
            /* White text color on hover */
        }


        .pagination-black .page-link {
            background-color: black;
            /* Default background color */
            color: white;
            /* Text color */
            border: 1px solid black;
            /* Border color */
            padding: 10px 20px;
            /* Padding for larger size */
            font-size: 18px;
            /* Font size */
        }

        .pagination-black .page-link:hover {
            background-color: #333;
            /* Darker shade on hover */
            color: white;
            /* Text color */
        }

        .pagination-black .page-item.active .page-link {

            background-color: #8c1c1c;
            /* Change this color to the desired active background color */
            border-color: #8c1c1c;
            /* Change the border color if needed */
            color: #fff;
            /* Active page text color */
        }

        .pagination-black .page-link:focus {
            box-shadow: none;
            /* Remove focus outline */
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

            <div id="header-table-title">Login Attempts Reports</div>
           

            <div class="employee-table-container">
                <div class="combo-box">
                    <label for="sort">Sort by Email Name: </label>
                    <select id="sort-name" onchange="sortTable()">
                        <option value="a-z">A-Z</option>
                        <option value="z-a">Z-A</option>
                    </select>
                  
                </div>
                <?php

                $limit = 7;


                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;

                // Count the total number of employees
                $count_sql = "SELECT COUNT(*) AS total FROM login_attempts_log";
                $count_result = $conn->query($count_sql);
                $total_rows = $count_result->fetch_assoc()['total'];
                $total_pages = ceil($total_rows / $limit);

                $sql = "SELECT 
                        log_id, email, action_details, log_date 
                        FROM 
                            login_attempts_log
                            ORDER BY log_id DESC
                        LIMIT 
                            $limit OFFSET $offset";

                $result = $conn->query($sql);
                ?>


                <div class="table-responsive">
                    <table class="table table-hover" id="payrollTable">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>EMAIL</th>
                                <th>FAILED ATTEMPT</th>
                                <th>LAST ATTEMPT</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($attempt = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($attempt['log_id']); ?></td>
                                        <td><?php echo htmlspecialchars($attempt['email']); ?></td>
                                        <td><?php echo htmlspecialchars($attempt['action_details']); ?></td>
                                         <td><?php echo date('F j, Y h:i A', strtotime($attempt['log_date'])); ?></td>

                                        
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No data found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>


        
          



        <!-- Pagination -->
        <nav aria-label="Employee Table Pagination">
            <ul class="pagination pagination-black justify-content-center">
                <!-- Previous Page Link -->
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <a class="page-link" href="#" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Page Links -->
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Next Page Link -->
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <a class="page-link" href="#" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
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





           

            // sort table by Name
            function sortTable() {
                var table, rows, switching, i, x, y, shouldSwitch;
                table = document.getElementById("payrollTable");
                switching = true;

                var sortOption = document.getElementById("sort-name").value;

                while (switching) {
                    switching = false;
                    rows = table.rows;

                    for (i = 1; i < (rows.length - 1); i++) {
                        shouldSwitch = false;


                        x = rows[i].getElementsByTagName("TD")[1];
                        y = rows[i + 1].getElementsByTagName("TD")[1];

                        if (sortOption === "a-z") {

                            if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                                shouldSwitch = true;
                                break;
                            }
                        } else if (sortOption === "z-a") {

                            if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                                shouldSwitch = true;
                                break;
                            }
                        }
                    }

                    if (shouldSwitch) {

                        rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                        switching = true;
                    }
                }
            }
        </script>
        


</body>

</html>