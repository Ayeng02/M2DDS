<?php
session_start();
include '../includes/db_connect.php';

// Fetch Active Employees from emp_tbl
// Query to fetch Active Employees with their latest granted access control details
$ACquery = "
    SELECT e.emp_id, 
           CONCAT(e.emp_fname, ' ', e.emp_lname) AS emp_name,
           COALESCE(ac.add_product, 'Disabled') AS add_product,
           COALESCE(ac.edit_product, 'Disabled') AS edit_product,
           COALESCE(ac.add_category, 'Disabled') AS add_category
    FROM emp_tbl e
    LEFT JOIN (
        SELECT ac1.emp_id, ac1.add_product, ac1.edit_product, ac1.add_category
        FROM access_control ac1
        INNER JOIN (
            SELECT emp_id, MAX(granted_date) AS latest_date
            FROM access_control
            GROUP BY emp_id
        ) latest
        ON ac1.emp_id = latest.emp_id AND ac1.granted_date = latest.latest_date
    ) ac ON e.emp_id = ac.emp_id
    WHERE e.emp_status = 'Active' AND e.emp_role = 'Order Manager'
    ORDER BY e.emp_fname ASC
";
$ACresult = mysqli_query($conn, $ACquery);

// Query to get the total number of records in the access_control table
$total_records_query = "SELECT COUNT(*) AS total FROM access_control";
$total_records_result = mysqli_query($conn, $total_records_query);
$total_records = mysqli_fetch_assoc($total_records_result)['total'];

// Dynamically set the number of records per page
$records_per_page = $total_records < 1000 ? $total_records : 200;  // Show all records if less than 1000, otherwise paginate with 200

// Determine the current page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Ensure the current page is at least 1

// Calculate the offset
$offset = ($current_page - 1) * $records_per_page;

// Calculate the total number of pages
$total_pages = ceil($total_records / $records_per_page);

// Query to fetch records for the current page
$CPquery = "SELECT ac.id, ac.emp_id, CONCAT(e.emp_fname, ' ', e.emp_lname) AS emp_name, ac.granted_date, e.emp_role,
                 ac.add_product, ac.edit_product, ac.add_category 
          FROM access_control ac
          JOIN emp_tbl e ON ac.emp_id = e.emp_id
          ORDER BY ac.granted_date DESC
          LIMIT $records_per_page OFFSET $offset";
$CPresult = mysqli_query($conn, $CPquery);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Interface</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <link rel="stylesheet" href="../css/admin.css">
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

            <div class="container mt-4">
                <h4>Role-Based Access Control</h4>

                <!-- Card Layout for Role-Based Access Control -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5>Employee Access Control</h5>
                    </div>
                    <div class="card-body">
                        <!-- Form to Display Active Employees with Access Control -->
                        <form id="accessControlForm" method="POST" action="save_access_control.php">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Add Product</th>
                <th>Edit Product</th>
                <th>Add Category</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($ACresult)): ?>
                <tr>
                    <td><?php echo $row['emp_id']; ?></td>
                    <td><?php echo $row['emp_name']; ?></td>

                    <td>
                        <select name="add_product_<?php echo $row['emp_id']; ?>" class="form-control">
                            <option value="Enabled" <?php echo ($row['add_product'] === 'Enabled') ? 'selected' : ''; ?>>Enabled</option>
                            <option value="Disabled" <?php echo ($row['add_product'] === 'Disabled') ? 'selected' : ''; ?>>Disabled</option>
                        </select>
                    </td>
                    <td>
                        <select name="edit_product_<?php echo $row['emp_id']; ?>" class="form-control">
                            <option value="Enabled" <?php echo ($row['edit_product'] === 'Enabled') ? 'selected' : ''; ?>>Enabled</option>
                            <option value="Disabled" <?php echo ($row['edit_product'] === 'Disabled') ? 'selected' : ''; ?>>Disabled</option>
                        </select>
                    </td>
                    <td>
                        <select name="add_category_<?php echo $row['emp_id']; ?>" class="form-control">
                            <option value="Enabled" <?php echo ($row['add_category'] === 'Enabled') ? 'selected' : ''; ?>>Enabled</option>
                            <option value="Disabled" <?php echo ($row['add_category'] === 'Disabled') ? 'selected' : ''; ?>>Disabled</option>
                        </select>
                    </td>
                    <td>
                        <button type="submit" name="save_<?php echo $row['emp_id']; ?>" class="btn btn-danger">
                            <i class="fas fa-floppy-disk"></i> Save
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</form>
                    </div>
                </div>
            </div>

            <div class="container mt-4">
                <h4>Access Control Log</h4>

                <!-- Search bar with icon -->
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by Employee ID, Name, or Month" onkeyup="searchTable()">
                </div>
                <!-- Card Layout for the Access Control Logs -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5>Employee Access Control Log</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered" id="accessTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th> <!-- Custom Incremented ID -->
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Add Product</th>
                                    <th>Edit Product</th>
                                    <th>Add Category</th>
                                    <th>Granted Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = $offset + 1; // Start counting from the current offset
                                while ($row = mysqli_fetch_assoc($CPresult)): ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td> <!-- Custom Incremented ID -->
                                        <td><?php echo $row['emp_id']; ?></td>
                                        <td><?php echo $row['emp_name']; ?></td>
                                        <td><?php echo ucfirst($row['add_product']); ?></td>
                                        <td><?php echo ucfirst($row['edit_product']); ?></td>
                                        <td><?php echo ucfirst($row['add_category']); ?></td>
                                        <td><?php echo date("F d, Y h:i A", strtotime($row['granted_date'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div class="card-footer">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php if ($i == $current_page) echo 'active'; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
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

        // SweetAlert confirmation before submitting the form
        $('#accessControlForm').on('submit', function(event) {
            event.preventDefault(); // Prevent form submission

            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to save access control changes.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('Form submitted'); // Debugging
                    // If confirmed, submit the form
                    this.submit();
                }
            });
        });

        function searchTable() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#accessTable tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(input) ? '' : 'none';
            });
        }

        // Pagination Setup
        document.addEventListener('DOMContentLoaded', () => {
            const rowsPerPage = 10;
            const table = document.querySelector("#accessTable tbody");
            const rows = Array.from(table.rows);
            const pagination = document.querySelector(".pagination");

            function displayPage(page) {
                rows.forEach((row, index) => {
                    row.style.display = (index >= (page - 1) * rowsPerPage && index < page * rowsPerPage) ? '' : 'none';
                });
            }

            function setupPagination() {
                const pageCount = Math.ceil(rows.length / rowsPerPage);
                pagination.innerHTML = '';
                for (let i = 1; i <= pageCount; i++) {
                    const li = document.createElement('li');
                    li.classList.add('page-item');
                    li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                    li.addEventListener('click', (e) => {
                        e.preventDefault();
                        displayPage(i);
                        document.querySelector('.page-item.active')?.classList.remove('active');
                        li.classList.add('active');
                    });
                    pagination.appendChild(li);
                }
                pagination.querySelector('.page-item')?.classList.add('active');
                displayPage(1);
            }

            setupPagination();
        });
    </script>

</body>

</html>