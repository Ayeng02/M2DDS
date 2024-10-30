<?php
// Set error reporting to ignore notices
error_reporting(E_ALL & ~E_NOTICE);

session_start();
include '../includes/db_connect.php';
include '../includes/sf_getEmpInfo.php';

// Fetch emp_id from session
$emp_id = $_SESSION['emp_id'];

// Pagination settings
$limit = 10; // Number of entries to show per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page number
$offset = ($page - 1) * $limit; // Calculate the offset for the SQL query

// SQL query to fetch logs with pagination
$query = "SELECT p.*, pr.prod_name, pr.prod_price
          FROM pos_tbl p 
          JOIN product_tbl pr ON p.prod_code = pr.prod_code 
          WHERE p.pos_personnel = '$emp_id' 
          ORDER BY p.transac_date DESC 
          LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

// Count total records for pagination
$countQuery = "SELECT COUNT(*) AS total FROM pos_tbl WHERE pos_personnel = '$emp_id'";
$countResult = $conn->query($countQuery);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS | My Logs</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/cashier.css">

    <style>
        .act5 {
            color: #A72828;
            font-weight: bold;
        }

        .card {
            width: 100%;
            /* Make the card full width */
            border-radius: 0.5rem;
            /* Adjust border radius for better aesthetics */
            overflow: hidden;
            /* Ensure content doesn't overflow */
            min-height: 400px;
        }

        .card-header {
            background-color: #A72828;
            color: white;
        }

        .table thead th {
            position: sticky;
            /* Make the header sticky */
            top: 0;
            /* Stick to the top of the container */
            background-color: #FF8225;
            /* Match the header background */
            color: white;
            /* Text color for the header */
            z-index: 10;
            /* Ensure the header is above other content */
        }

        .table-container {
            max-height: 400px;
            /* Set the maximum height for the scrolling area */
            overflow-y: auto;
            /* Enable vertical scrolling */
            margin-bottom: 20px;
            /* Add some space below the table */
        }

        .table {
            width: 100%;
            /* Make the table full width */
            border-collapse: collapse;
            /* Ensure there are no gaps between cells */
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f2f2f2;
        }

        .search-container {
            margin-bottom: 20px;
        }

        .input-group {
            display: flex;
            align-items: center;
        }

        .input-group input {
            border-radius: 5px 0 0 5px;
            /* Round the left corners */
            border: 1px solid #ccc;
            height: 38px;
            width: 100%;
            /* Make sure it takes full width */
            transition: border-color 0.3s;
        }

        .input-group input:focus {
            border-color: #A72828;
            outline: none;
        }

        .input-group .input-group-text {
            background-color: #FF8225;
            border: 1px solid #A72828;
            /* Match the border with the input */
            border-left: none;
            /* Remove the left border to blend with the input */
            color: white;
            /* Change icon color to white for better visibility */
            border-radius: 0 5px 5px 0;
            /* Round the right corners */
        }

        .input-group .search-icon i {
            color: white;
            /* Ensure the icon is white */
        }

        .navigation {
            margin-top: 15px;
        }
        .highlight {
    background-color: #ccc !important; /* Ensure it overrides other styles */
}

    </style>

</head>

<body>

    <?php include '../includes/cashierHeader.php'; ?>

    <div class="title" style="margin-top: 120px;">
        <img src="../img/mtdd_logo.png" alt="Logo">
        My Logs Report
    </div>

    <div class="container my-5">

        <!-- Logs Table Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Transaction Logs</h5>
            </div>
            <div class="card-body p-3"> <!-- Added padding here -->
                <!-- Search Bar -->
                <div class="search-container mb-3">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by Transaction Code, Product Code, etc." onkeyup="searchTable()">
                        <div class="input-group-append">
                            <span class="input-group-text search-icon"><i class="bi bi-search"></i></span>
                        </div>
                    </div>
                </div>
                <div class="download-buttons mb-3">
                    <button class="btn btn-success" onclick="downloadExcel()"> <i class="bi bi-box-arrow-down"></i> Excel</button>
                    <button class="btn btn-danger" onclick="downloadPDF()"> <i class="bi bi-box-arrow-down"></i> PDF</button>
                </div>

                <div class="table-responsive table-container">
                    <table class="table table-striped table-hover" id="logsTable">
                        <thead>
                            <tr>
                                <th>Transaction Code</th>
                                <th>Product Code</th>
                                <th>Product Name</th> <!-- New Column -->
                                <th>Quantity</th>
                                <th>Discount</th>
                                <th>Total Amount</th>
                                <th>Amount Received</th>
                                <th>Change</th>
                                <th>Price</th> <!-- New Column -->
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['pos_code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['prod_code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['prod_name']); ?></td> <!-- Displaying Product Name -->
                                        <td><?php echo htmlspecialchars($row['pos_qty']); ?></td>
                                        <td><?php echo htmlspecialchars($row['pos_discount']); ?></td>
                                        <td><?php echo htmlspecialchars($row['total_amount']); ?></td>
                                        <td><?php echo htmlspecialchars($row['amount_received']); ?></td>
                                        <td><?php echo htmlspecialchars($row['pos_change']); ?></td>
                                        <td><?php echo htmlspecialchars($row['prod_price']); ?></td> <!-- Displaying Product Price -->
                                        <td><?php echo date('F j, Y h:i A', strtotime($row['transac_date'])); ?></td>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/shortcutNavigator.js" ></script>

    <script>
        // Function to update the time
        function updateTime() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const formattedDate = now.toLocaleDateString('en-US', options);
            const formattedTime = now.toLocaleTimeString('en-US');
            document.getElementById('currentTime').innerText = `${formattedDate}, ${formattedTime}`;
        }

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
            window.location.href = 'download_excel.php';
        }

        function downloadPDF() {
            window.location.href = 'download_pdf.php';
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
            block: 'center'     // Align the row to the center of the viewport
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

</body>

</html>