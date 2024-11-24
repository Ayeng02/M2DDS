
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
    <title>Admin Interface</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
     <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <!-- Custom CSS -->
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
            height: 100%; /* Full viewport height */
        }
        #sidebar-wrapper {
    min-height: 100vh;
    width: 80px; /* Default width for icons only */
    background-color: #a72828;
    color: #fff;
    transition: width 0.3s ease;
    overflow-y: auto; /* Allow vertical scrolling */
    position: relative;
    overflow-x: hidden; /* Prevent horizontal scrolling */
    border-right: 1px solid #ddd; /* Light border to separate from content */
    box-shadow: 2px 0 5px rgba(0,0,0,0.1); /* Subtle shadow */
  
}
#sidebar-wrapper.expanded {
    width: 250px; /* Expanded width */
}
#sidebar-wrapper .sidebar-heading {
    padding: 1rem;
    display: flex;
    align-items: center;
    background-color: #FF8225;
    color: #fff;
    border-bottom: 1px solid #ddd; /* Border for separation */
}
#sidebar-wrapper .logo-img {
    width: 40px; /* Adjust size as needed */
    height: 40px;
    margin-right: 10px; /* Space between logo and text */
}
#sidebar-wrapper .sidebar-title {
    font-size: 1.5rem;
    display: inline; /* Ensure title is always visible */
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
            border-radius: 0; /* Remove default border radius */
            transition: background-color 0.2s ease; /* Smooth hover effect */
        }
        #sidebar-wrapper .list-group-item i {
            font-size: 1.5rem;
            margin-right: 15px;
        }
        #sidebar-wrapper .list-group-item span {
    display: none; /* Hide text in default state */
    margin-left: 10px;
    white-space: nowrap; /* Prevent text wrapping */
}
#sidebar-wrapper.expanded .list-group-item span {
    display: inline; /* Show text in expanded state */
}
        #sidebar-wrapper .list-group-item:hover {
            background-color: #8c1c1c; /* Darker color on hover */
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
            box-shadow: 0 0 5px rgba(0,0,0,0.2); /* Button shadow */
        }
        #sidebar-wrapper .toggle-btn:hover {
            background-color: #a72828;
        }
        #page-content-wrapper {
            flex: 1;
            padding: 20px;
            transition: margin-left 0.3s ease;
            background-color: #f8f9fa; /* Slightly different background */
        }
        #page-content-wrapper.sidebar-expanded {
            margin-left:0px; /* Match the expanded sidebar width */
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
    width: 30px; /* Adjust size when collapsed */
    height: 30px;
}



.order-table-container {
    display: flex;
    flex-direction: row;
    width: 95%;
    height: 65vh; 
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

.Category-table {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: auto;

}

.Category-table h2 {
    margin-bottom: 10px;
    color: #007bff;
    text-align: center;
}
.Category-table span{
    font-size: 30px;
}

.Category-table table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed; 
}

.Category-table thead {
    background-color: #007bff;
    color: white;
    font-size: 16px;
    text-transform: uppercase;
    text-align: center;
      position: sticky;
      
}

.Category-table tbody {
    display: block;
    overflow-y: auto; 
    height: calc(100% - 45px);
}

.Category-table thead, .Category-table tbody tr {
    display: table;
    width: 100%; 
    table-layout: fixed; 
}

.Category-table th, .Category-table td {
    padding: 12px;
    border: 1px solid #dee2e6;
    text-align: center;
    white-space: nowrap;
    font-size: 16px;
}

.Category-table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

.Category-table tbody tr:hover {
    background-color: #e2e6ea;
}
#header-table-title{
    text-align: center;
    font-size: 50px;
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
.combo-box label, .combo-box select, input {
    font-size: 16px;
    margin-left: 2px;
}

.combo-box label {
    font-weight: bold;
    margin-right: 10px;
}

.combo-box select , input {
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

.combo-box select:focus, input:focus {
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

    
    .delete-icon i {
        color: #dc3545; 
        font-size: 16px; 
        cursor: pointer;
        transition: color 0.2s ease;
    }
    
    .delete-icon i:hover {
        color: #c82333; 
    }
   #copyTableBtn{
    margin-left: 2%;
    color: white;
   } 
   
   .table-header{
    background-color: #8c1c1c;
   }
    nav{
    margin-top: 1%;
   }
   .pagination-black .page-link {
    background-color: black;   /* Default background color */
    color: white;              /* Text color */
    border: 1px solid black;   /* Border color */
    padding: 10px 20px;        /* Padding for larger size */
    font-size: 18px;           /* Font size */
}

.pagination-black .page-link:hover {
    background-color: #333;    /* Darker shade on hover */
    color: white;               /* Text color */
}

.pagination-black .page-item.active .page-link {
    background-color: #FF8225; /* Change this color to the desired active background color */
    border-color: #FF8225;     /* Change the border color if needed */
    color: #fff;                /* Active page text color */
}

.pagination-black .page-link:focus {
    box-shadow: none;           /* Remove focus outline */
}
#searchInput {
    max-width: 250px;
    height: 42px;
}

#searchButton {
    margin-left: 10px;
    height: 42px;
    width: 120px;
}
.navbar{
    margin-right: 50px;
    margin-bottom: 10px;
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
      

    
        
             <div id="header-table-title">CASHIER</div>
            <div class="modal fade" id="copyModal" tabindex="-1" aria-labelledby="copyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                <div class="modal-body text-center">
                    Copied to clipboard!
                </div>
                </div>
            </div>
            </div>
            
            
           <button id="copyTableBtn" class="btn btn-info"><i class="fas fa-copy"></i>  Copy to Clipboard</button>
           <button id="downloadPDF" class="btn btn-danger "> <i class="fas fa-file-pdf"></i> Download as PDF</button>
              <button id="downloadExcel" class="btn btn-success"><i class="fas fa-file-excel"></i> Download as Excel</button>
                    <nav class="navbar navbar-light bg-light" style="float: right;">
            <form class="form-inline" id="searchForm" onsubmit="return false;"> <!-- Prevent form submission -->
                <input class="form-control mr-sm-2" type="search" id="searchInput" placeholder="Search" aria-label="Search">
                <button class="btn btn-primary my-2 my-sm-0" id="searchButton" type="button">Search</button> <!-- Change type to button -->
            </form>
        </nav>

            <div class="order-table-container">
               <div class="combo-box">
                <label for="sort">Sort by Product Name: </label>
                <select id="sort-name" onchange="sortTable()">
                    <option value="a-z">A-Z</option>
                    <option value="z-a">Z-A</option>
                </select>
                <label for="transacDate">Select Transaction Date:</label>
                <input type="date" id="transacDate" onchange="filterTransactByDate()">
                 
    </select>
            </div>
               <?php
                    $limit = 9;
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $offset = ($page - 1) * $limit;

                    // Get the selected order date
                    $transac_date = isset($_GET['transac_date']) ? $_GET['transac_date'] : '';

                    // Prepare the count SQL with date filtering
                    $count_sql = "SELECT COUNT(*) AS total FROM pos_tbl";
                    if (!empty($transac_date)) {
                        $count_sql .= " WHERE DATE(transac_date) = '" . $conn->real_escape_string($transac_date) . "'";
                    }

                    $count_result = $conn->query($count_sql);
                    $total_rows = $count_result->fetch_assoc()['total'];
                    $total_pages = ceil($total_rows / $limit);

                    // Fetch orders for the current page with date filtering
                    $sql = "SELECT p.pos_code, pp.prod_name, p.pos_qty, 
                            p.pos_discount, p.total_amount, p.amount_received, p.pos_change, CONCAT(e.emp_fname, '' , e.emp_lname) as fullname, p.transac_date
                            FROM pos_tbl p
                            JOIN emp_tbl e ON p.pos_personnel = e.emp_id
                            JOIN product_tbl pp ON p.prod_code = pp.prod_code";


                    $conditions = [];
                    
                    if (!empty($transac_date)) {
                        $conditions[] = "WHERE DATE(p.transac_date) = '" . $conn->real_escape_string($transac_date) . "'";
                    }
                    
                    // Append conditions to the SQL query
                    if (!empty($conditions)) {
                        $sql .= " WHERE " . implode(' AND ', $conditions);
                    }

                    $sql .= " ORDER BY p.pos_code DESC 
                            LIMIT $limit OFFSET $offset";

                    $result = $conn->query($sql);
                    ?>
            

                <div class="table-responsive">
                    <table class="table table-hover" id="orderTable">
                        <thead class="table-dark">
                            <tr>
                                <th>POS CODE</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Discount</th>
                                <th>Total Amount</th>
                                <th>Amount Receive</th>
                                <th>Change</th>
                                <th>Cashier Name</th>
                                <th>Date</th>
                                <th>Action</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($pos = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pos['pos_code']); ?></td>
                                    <td><?php echo htmlspecialchars($pos['prod_name']); ?></td>
                                    <td><?php echo htmlspecialchars($pos['pos_qty']); ?></td>
                                    <td><?php echo htmlspecialchars($pos['pos_discount']); ?></td>
                                    <td><?php echo htmlspecialchars($pos['total_amount']); ?></td>
                                    <td><?php echo htmlspecialchars($pos['amount_received']); ?></td>
                                     <td><?php echo htmlspecialchars($pos['pos_change']); ?></td>
                                    <td><?php echo htmlspecialchars($pos['fullname']); ?></td>
                                     <td><?php echo date('F j, Y h:i A', strtotime($pos['transac_date'])); ?></td>
                                    
                                    
                                    <td>
                                        <!-- Edit button trigger modal -->
                                        <a href="#" class="edit-icon" data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-pos-id="<?php echo htmlspecialchars($pos['pos_code']); ?>"
                                            data-prod-name="<?php echo htmlspecialchars($pos['prod_name']); ?>"
                                            data-qty="<?php echo htmlspecialchars($pos['pos_qty']); ?>"
                                            data-discount="<?php echo htmlspecialchars($pos['pos_discount']); ?>"
                                            data-total="<?php echo htmlspecialchars($pos['total_amount']); ?>"
                                            data-recieve="<?php echo htmlspecialchars($pos['amount_received']); ?>"
                                            data-change="<?php echo htmlspecialchars($pos['pos_change']); ?>"
                                            data-fullname="<?php echo htmlspecialchars($pos['fullname']); ?>"
                                            data-date="<?php echo date('F j, Y h:i A', strtotime($pos['transac_date'])); ?>"
                                            >
                                            <i class="fa fa-edit"></i>
                                        </a>

                                        <!-- Delete button -->
                                        <a href="delete_pos.php?id=<?php echo htmlspecialchars($pos['pos_code']); ?>" class="delete-icon" onclick="confirmDelete(event, '<?php echo htmlspecialchars($pos['pos_code']); ?>')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                 
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="10">No transaction found.</td></tr>
                            <?php endif; ?>
                            
                        </tbody>
                    </table>
                </div>
                       

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
           <form class="row g-3" id="editOrderForm" method="post" action="update_pos.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit POS Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="pos_code" id="edit-pos-code">

                    <div class="mb-3">
                        <label for="edit-prod-name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="prod_name" id="edit-prod-name" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="edit-qty" class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="pos_qty" id="edit-qty" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-discount" class="form-label">Discount</label>
                        <input type="text" class="form-control" name="pos_discount" id="edit-discount" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-total" class="form-label">Total Amount</label>
                        <input type="text" class="form-control" name="total_amount" id="edit-total" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="edit-receive" class="form-label">Amount Received</label>
                        <input type="text" class="form-control" name="amount_receive" id="edit-receive" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-change" class="form-label">Change</label>
                        <input type="text" class="form-control" name="pos_change" id="edit-change" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="edit-fullname" class="form-label">Cashier Name</label>
                        <input type="text" class="form-control" name="fullname" id="edit-fullname" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="edit-date" class="form-label">Date</label>
                        <input type="text" class="form-control" name="transac_date" id="edit-date" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


        <?php
        // Check if the 'success' parameter exists in the URL

        if (isset($_SESSION['success'])) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Updated',
                        text: '" . $_SESSION['success'] . "',
                        confirmButtonText: 'OK'
                    });
                });
            </script>";
            unset($_SESSION['success']);
        }
        ?>
        <?php
        // Check if the 'delete_success' parameter exists in the URL
       if (isset($_SESSION['delete_success'])) {
    echo '<script>
        Swal.fire({
            icon: "success",
            title: "Success",
            text: "' . $_SESSION['delete_success'] . '",
            confirmButtonText: "OK"
        });
    </script>';
    unset($_SESSION['delete_success']); // Clear the message after displaying
} elseif (isset($_SESSION['delete_error'])) {
    echo '<script>
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "' . $_SESSION['delete_error'] . '",
            confirmButtonText: "OK"
        });
    </script>';
    unset($_SESSION['delete_error']); // Clear the message after displaying
}
        ?>
            </div>
           <nav aria-label="Order Table Pagination"> 
    <ul class="pagination pagination-black justify-content-center">
        <!-- Previous Page Link -->
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>&transac_date=<?php echo urlencode($transac_date ?? ''); ?>" aria-label="Previous">
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
                <a class="page-link" href="?page=<?php echo $i; ?>&transac_date=<?php echo urlencode($transac_date ?? ''); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>

        <!-- Next Page Link -->
        <?php if ($page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>&transac_date=<?php echo urlencode($transac_date ?? ''); ?>" aria-label="Next">
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
    </div>
    <!-- /#page-content-wrapper -->
</div>
</div>
<!-- /#wrapper -->

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

//search for the code, prodname, and cashier name
document.getElementById('searchButton').addEventListener('click', function() {
    let filter = document.getElementById('searchInput').value.toUpperCase();
    let rows = document.querySelectorAll('#orderTable tbody tr');
    
    rows.forEach(row => {
        let posCode = row.cells[0].textContent.toUpperCase();
        let prodName = row.cells[1].textContent.toUpperCase();
        let cashierName = row.cells[7].textContent.toUpperCase();
        
        if (posCode.indexOf(filter) > -1 || prodName.indexOf(filter) > -1 || cashierName.indexOf(filter) > -1) {
            row.style.display = ''; // Show row
        } else {
            row.style.display = 'none'; // Hide row
        }
    });
});



 // Add event listener to populate the modal with data
    document.addEventListener('DOMContentLoaded', () => {
        const editButtons = document.querySelectorAll('.edit-icon');

        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                const posId = button.getAttribute('data-pos-id');
                const prodName = button.getAttribute('data-prod-name');
                const qty = button.getAttribute('data-qty');
                const discount = button.getAttribute('data-discount');
                const total = button.getAttribute('data-total');
                const receive = button.getAttribute('data-recieve');
                const change = button.getAttribute('data-change');
                const fullname = button.getAttribute('data-fullname');
                const date = button.getAttribute('data-date');

                document.getElementById('edit-pos-code').value = posId;
                document.getElementById('edit-prod-name').value = prodName;
                document.getElementById('edit-qty').value = qty;
                document.getElementById('edit-discount').value = discount;
                document.getElementById('edit-total').value = total;
                document.getElementById('edit-receive').value = receive;
                document.getElementById('edit-change').value = change;
                document.getElementById('edit-fullname').value = fullname;
                document.getElementById('edit-date').value = date;
            });
        });
    });
    //filter by order date
    
function filterTransactByDate(page = 1) {
    const transacDate = document.getElementById('transacDate').value;

    var xhr = new XMLHttpRequest();
    const url = `filterTransactDate.php?page=${page}&transac_date=${encodeURIComponent(transacDate)}`;

    xhr.open('GET', url, true);
    
    xhr.onload = function () {
        if (this.status === 200) {
            // Parse the response text to find the specific sections
            var responseText = this.responseText;

            // Extract table rows
            var tbodyContent = responseText.match(/<tbody id="order-rows">[\s\S]*?<\/tbody>/)[0];
            // Extract pagination links
            var paginationContent = responseText.match(/<nav aria-label="Order Table Pagination">[\s\S]*?<\/nav>/)[0];

            // Replace the table body with the new rows
            document.querySelector('tbody').outerHTML = tbodyContent;

            // Replace the pagination section with the new pagination
            document.querySelector('nav[aria-label="Order Table Pagination"]').outerHTML = paginationContent;
        }
    };

    xhr.send();
}



//copy in clipboard
document.getElementById('copyTableBtn').addEventListener('click', function() {
    var table = document.getElementById('orderTable');
    var range, selection, body = document.body;

    // Create a temporary textarea to store the table content as plain text
    var tempTextarea = document.createElement('textarea');
    var tableContent = '';

    // Iterate over table rows and cells to create a plain text version of the table
    for (var i = 0; i < table.rows.length; i++) {
        var row = table.rows[i];
        for (var j = 0; j < row.cells.length; j++) {
            tableContent += row.cells[j].innerText + '\t';  // Add tabs between columns
        }
        tableContent += '\n';  // Add new line between rows
    }

    // Add table content to the textarea
    tempTextarea.value = tableContent;
    body.appendChild(tempTextarea);

    // Select and copy the content from the textarea
    tempTextarea.select();
    document.execCommand('copy');

    // Remove the temporary textarea
    body.removeChild(tempTextarea);

    // Show the "Copied" modal
    var copyModal = new bootstrap.Modal(document.getElementById('copyModal'));
    copyModal.show();

    // Hide the modal after 1 second
    setTimeout(function() {
        copyModal.hide();
    }, 1000);
});
//download as excel
 document.getElementById('downloadExcel').addEventListener('click', function() {
        const table = document.getElementById('orderTable');
        const workbook = XLSX.utils.table_to_book(table, { sheet: "Products" });
        XLSX.writeFile(workbook, 'POS_table.xlsx');
    });
//Download as pdf
document.getElementById('downloadPDF').addEventListener('click', function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.autoTable({
        html: '#orderTable',
        startY: 20,
        theme: 'grid',
        headStyles: { fillColor: [0, 150, 0] },  // Custom header color
        margin: { top: 10 },
    });

    doc.save('POS_table.pdf');
});
// confirmation for deleting product
function confirmDelete(event, posCode) {
    event.preventDefault(); // Prevent default anchor behavior

    // SweetAlert confirmation
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            // If confirmed, proceed to delete
            window.location.href = 'delete_pos.php?id=' + posCode;
        }
    });
}





// sort table by Name
function sortTable() {
    var table, rows, switching, i, x, y, shouldSwitch;
    table = document.getElementById("orderTable");
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