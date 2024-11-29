<?php
// Set error reporting to ignore notices
error_reporting(E_ALL & ~E_NOTICE);

session_start();
include '../includes/db_connect.php';
include '../includes/sf_getEmpInfo.php';
?>
<?php
$emp_id = $_SESSION['emp_id'];

///Daily Sale
$sale_sql = "SELECT SUM(total_amount) AS daily_sales 
             FROM pos_tbl 
             WHERE DATE(transac_date) = CURDATE() 
             AND pos_personnel = ?";

$stmt = $conn->prepare($sale_sql);
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$sale_result = $stmt->get_result();

$daily_sales = 0;

if ($sale_result->num_rows > 0) {
    $row = $sale_result->fetch_assoc();
    $daily_sales = $row['daily_sales'] ?? 0;
}


/// Total sold
$totalQuan = "SELECT COUNT(pos_code) AS transac 
              FROM pos_tbl 
              WHERE MONTH(transac_date) = MONTH(CURDATE()) 
              AND YEAR(transac_date) = YEAR(CURDATE())
              AND pos_personnel = ?";

$stmt2 = $conn->prepare($totalQuan);
$stmt2->bind_param("s", $emp_id);
$stmt2->execute();
$total_result = $stmt2->get_result();

$count = 0;
if ($total_result->num_rows > 0) {
    $row = $total_result->fetch_assoc();
    $count = $row['transac'] ?? 0; // Correct the field name to 'transac'
}


///Monthly Sale
$monthSale = "SELECT SUM(total_amount) AS monthly_sales 
             FROM pos_tbl 
             WHERE MONTH(transac_date) = MONTH(CURDATE())
             AND YEAR(transac_date) = YEAR(CURDATE())
             AND pos_personnel = ?";

$stmt3 = $conn->prepare($monthSale);
$stmt3->bind_param("s", $emp_id);
$stmt3->execute();
$month_result = $stmt3->get_result();

$monthly_sales = 0;

if ($month_result->num_rows > 0) {
    $row = $month_result->fetch_assoc();
    $monthly_sales = $row['monthly_sales'] ?? 0;
}


// Pagination settings
$limit = 10; // Number of entries to show per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page number
$offset = ($page - 1) * $limit; // Calculate the offset for the SQL query


/// Sales Breakdown

// Retrieve necessary product sales records
$query = "SELECT p.pos_personnel, p.prod_code, pr.prod_name, pr.prod_price, SUM(p.total_amount) AS total_sales
          FROM pos_tbl p
          JOIN product_tbl pr ON p.prod_code = pr.prod_code
          WHERE p.pos_personnel = '$emp_id'
          GROUP BY p.pos_personnel, p.prod_code, pr.prod_name, pr.prod_price
          ORDER BY total_sales DESC
          LIMIT $limit OFFSET $offset";

$result = $conn->query($query);

// Count total records for pagination (after grouping, count distinct product codes)
$countQuery = "SELECT COUNT(DISTINCT p.prod_code) AS total FROM pos_tbl p WHERE p.pos_personnel = '$emp_id'";
$countResult = $conn->query($countQuery);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.bootstrap5.css">

    <link rel="stylesheet" href="../css/cashier.css">
    <link rel="stylesheet" href="../css/cashSales.css">

    <style>
        .act3 {
            color: #A72828;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include '../includes/cashierHeader.php'; ?>

    <div class="title">
        <img src="../img/mtdd_logo.png" alt="Logo">
        Meat-To-Door Sales
    </div>

    <div class="container-fluid conTop">
        <div class="cards">
            <div class="card text-bg-primary mb-3" style="max-width: 30rem;">
                <div class="card-body" id="card_con">
                    <div class="content">
                        <i class="fa-solid fa-wallet"></i>
                        <div class="info">
                            <p class="ctitle">Daily Sales</p>
                            <h2 class="card-text">₱ <?php echo number_format($daily_sales, 2); ?></h2>
                            <h5 class="card-text"><?php echo date('F j, Y'); ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card text-bg-success mb-3" style="max-width: 30rem;">
                <div class="card-body" id="card_con">
                    <div class="content">
                        <i class="bi bi-bag-check-fill"></i>
                        <div class="info">
                            <p class="ctitle">Total Transaction</p>
                            <h2 class="card-text"> <?php echo number_format($count); ?> </h2>
                            <h5><?php echo date('F - Y'); ?></h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card text-bg-danger mb-3" style="max-width: 30rem;">
                <div class="card-body" id="card_con">
                    <div class="content">
                        <i class="bi bi-calendar3"></i>
                        <div class="info">
                            <p class="ctitle"> Monthly Sales</p>
                            <h2 class="card-text"> <?php echo number_format($monthly_sales, 2); ?></h2>
                            <h5><?php echo date('F - Y'); ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <br>
    <div class="container my-5 maincontanier">

        <!-- Logs Table Card -->
        <div class="card con">
            <div class="card-header">
                <h5 class="mb-0">Product Sale Records</h5>
            </div>
            <div class="card-body p-3"> <!-- Added padding here -->
                <!-- Search Bar -->
                <div class="search-container mb-3">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search" onkeyup="searchTable()">
                        <div class="input-group-append">
                            <span class="input-group-text search-icon"><i class="bi bi-search"></i></span>
                        </div>
                    </div>
                </div>


                <div class="container mb-1" style="margin-top: 20px;">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Flexbox container for buttons and date picker -->
                            <div class="d-flex align-items-center download-buttons mb-3">
                                <button class="btn btn-success" onclick="downloadExcel()">
                                    <i class="bi bi-box-arrow-down"></i> Excel
                                </button>
                                <button class="btn btn-danger ms-2" onclick="downloadPDF()">
                                    <i class="bi bi-box-arrow-down"></i> PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="table-responsive table-container">
                    <table class="table table-striped table-hover" id="logsTable">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Total Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['pos_personnel']); ?></td>
                                        <td><?php echo htmlspecialchars($row['prod_code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['prod_name']); ?></td> <!-- Displaying Product Name -->
                                        <td><?php echo htmlspecialchars($row['prod_price']); ?></td>
                                        <td><?php echo htmlspecialchars($row['total_sales']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">No logs found.</td> <!-- Adjust colspan -->
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <nav aria-label="Page navigation" class="navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>




    <!-- Footer -->
    <footer class="footer-widget text-center">
        <div class="container-fluid">
            <p id="currentTime" class="mb-1"></p>
            <p class="footer-text">Meat-To-Door 2024: Where Quality Meets Affordability</p>
        </div>
    </footer>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="../js/shortcutNavigator.js" ></script>





    <script>
        window.addEventListener('load', adjustFontSize);
        window.addEventListener('resize', adjustFontSize);

        function adjustFontSize() {
            const cardTexts = document.querySelectorAll('.card-text');
            cardTexts.forEach((cardText) => {
                const textLength = cardText.textContent.length;
                let fontSize = 2.3;

                // Dynamically reduce the font size based on text length
                if (textLength > 10) {
                    fontSize = 1.5;
                }
                if (textLength > 15) {
                    fontSize = 1.2;
                }

                cardText.style.fontSize = `${fontSize}rem`;
            });
        }
    </script>

    <script>
        function updateTime() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const formattedDate = now.toLocaleDateString(undefined, options);
            const formattedTime = now.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });

            document.getElementById('currentTime').textContent = formattedDate + ' | ' + formattedTime;
        }

        // Update time every second
        setInterval(updateTime, 1000);
        updateTime();


        // Search function for the table
        function searchTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("logsTable");
            const tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName("td");
                let rowContainsFilter = false;

                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            rowContainsFilter = true;
                            break;
                        }
                    }
                }

                tr[i].style.display = rowContainsFilter ? "" : "none"; // Show or hide the row
            }
        }

        function downloadExcel() {
            window.location.href = 'salesExcl.php';
        }

        function downloadPDF() {
            window.location.href = 'salesPdf.php';
        }

        let rows = document.querySelectorAll('#logsTable tbody tr');
        let currentIndex = -1; // Start with no row highlighted

        // Function to highlight a specific row
        function highlightRow(index) {
            // Remove highlight from all rows
            rows.forEach(row => row.classList.remove('highlight'));

            // Highlight the current row if within bounds
            if (index >= 0 && index < rows.length) {
                rows[index].classList.add('highlight');

                // Scroll the highlighted row into view
                rows[index].scrollIntoView({
                    behavior: 'smooth', // Smooth scrolling
                    block: 'center' // Align the row to the center of the viewport
                });
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            // Focus on search input with Alt + S
            if (event.altKey && event.key === 's') {
                event.preventDefault();
                document.getElementById('searchInput').focus();
            }

            // Start highlighting from the first row with Alt + H
            if (event.altKey && event.key === 't') {
                event.preventDefault();
                if (currentIndex === -1) {
                    currentIndex = 0; // Initialize highlighting on the first row
                }
                highlightRow(currentIndex);
            }

            // Navigate rows with up and down arrow keys
            if (event.key === 'ArrowDown') {
                event.preventDefault(); // Prevent default scrolling
                if (currentIndex < rows.length - 1) {
                    currentIndex++;
                    highlightRow(currentIndex);
                }
            } else if (event.key === 'ArrowUp') {
                event.preventDefault(); // Prevent default scrolling
                if (currentIndex > 0) {
                    currentIndex--;
                    highlightRow(currentIndex);
                }
            }

            // Navigate pagination with Alt + Left Arrow (Previous) and Alt + Right Arrow (Next)
            if (event.altKey && event.key === 'ArrowLeft') {
                event.preventDefault();
                const prevPageLink = document.querySelector('.pagination .page-item:not(.active) a[aria-label="Previous"]');
                if (prevPageLink) {
                    window.location.href = prevPageLink.href; // Navigate to the previous page
                }
            } else if (event.altKey && event.key === 'ArrowRight') {
                event.preventDefault();
                const nextPageLink = document.querySelector('.pagination .page-item:not(.active) a[aria-label="Next"]');
                if (nextPageLink) {
                    window.location.href = nextPageLink.href; // Navigate to the next page
                }
            }


            // Trigger Excel download with Alt + E
            if (event.altKey && event.key === 'e') {
                event.preventDefault(); // Prevent default action
                downloadExcel(); // Call the download function
            }

            // Trigger PDF download with Alt + P
            if (event.altKey && event.key === 'p') {
                event.preventDefault(); // Prevent default action
                downloadPDF(); // Call the download function
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#logoutBtn').on('click', function(e) {
                e.preventDefault(); // Prevent the default link behavior

                // Show SweetAlert confirmation before logging out
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You will be logged out of the system.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'Cancel',
                    reverseButtons: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Perform AJAX call to logout
                        $.ajax({
                            url: '../includes/logout.php', // Path to your logout PHP script
                            method: 'POST',
                            success: function() {
                                // Show success message and redirect to login page
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Logged out',
                                    text: 'You have been successfully logged out.',
                                    showConfirmButton: false,
                                    timer: 1000
                                }).then(() => {
                                    window.location.href = '../login.php'; // Redirect to login page
                                });
                            },
                            error: function() {
                                // Show error message if logout fails
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to log out. Please try again.',
                                });
                            }
                        });
                    }
                });
            });

        });
    </script>

</body>

</html>