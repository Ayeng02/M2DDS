
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
      

    
        
             <div id="header-table-title">Orders</div>
            <div class="modal fade" id="copyModal" tabindex="-1" aria-labelledby="copyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                <div class="modal-body text-center">
                    Copied to clipboard!
                </div>
                </div>
            </div>
            </div>
            <?php 
           $status_query = "SELECT DISTINCT status_name, status_code FROM status_tbl";
            $status_result = mysqli_query($conn, $status_query);

            if (!empty($status_code)) {
    $count_sql .= " WHERE o.status_code = '" . $conn->real_escape_string($status_code) . "'";
}
            ?>

           <button id="copyTableBtn" class="btn btn-info"><i class="fas fa-copy"></i>  Copy to Clipboard</button>
           <button id="downloadPDF" class="btn btn-danger "> <i class="fas fa-file-pdf"></i> Download as PDF</button>
              <button id="downloadExcel" class="btn btn-success"><i class="fas fa-file-excel"></i> Download as Excel</button>
            <div class="order-table-container">
               <div class="combo-box">
                <label for="sort">Sort by Buyer Name: </label>
                <select id="sort-name" onchange="sortTable()">
                    <option value="a-z">A-Z</option>
                    <option value="z-a">Z-A</option>
                </select>
                <label for="orderDate">Select Order Date:</label>
                <input type="date" id="orderDate" onchange="filterOrdersByDate()">
                 <label for="sort-category">Sort by Status: </label>  
                 <select id="sort-status" onchange="sortTableStatus()">
                    <option value="">All</option>
                    <?php
                // Loop through the result to generate status options
                while ($row = mysqli_fetch_assoc($status_result)) {
                echo '<option value="' . $row['status_code'] . '">' . $row['status_name'] . '</option>';
                
                }
                ?>
    </select>
            </div>
               <?php
                    $limit = 9;
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $offset = ($page - 1) * $limit;

                    // Get the selected order date
                    $order_date = isset($_GET['order_date']) ? $_GET['order_date'] : '';

                    // Prepare the count SQL with date filtering
                    $count_sql = "SELECT COUNT(*) AS total FROM order_tbl";
                    if (!empty($order_date)) {
                        $count_sql .= " WHERE DATE(order_date) = '" . $conn->real_escape_string($order_date) . "'";
                    }

                    $count_result = $conn->query($count_sql);
                    $total_rows = $count_result->fetch_assoc()['total'];
                    $total_pages = ceil($total_rows / $limit);

                    // Fetch orders for the current page with date filtering
                    $sql = "SELECT o.order_id, o.order_fullname, o.order_phonenum, 
                            CONCAT(o.order_purok, ' ', o.order_barangay, ', ', o.order_province) AS order_address,
                            o.order_mop, o.order_qty, o.order_total, o.order_cash, o.order_change, s.status_name
                            FROM order_tbl o
                            JOIN status_tbl s ON o.status_code = s.status_code";

                    $conditions = [];
                    if (!empty($status_code)) {
                        $conditions[] = "o.status_code = '" . $conn->real_escape_string($status_code) . "'";
                    }
                    if (!empty($order_date)) {
                        $conditions[] = "DATE(o.order_date) = '" . $conn->real_escape_string($order_date) . "'";
                    }

                    // Append conditions to the SQL query
                    if (!empty($conditions)) {
                        $sql .= " WHERE " . implode(' AND ', $conditions);
                    }

                    $sql .= " ORDER BY o.order_id DESC 
                            LIMIT $limit OFFSET $offset";

                    $result = $conn->query($sql);
                    ?>
            

                <div class="table-responsive">
                    <table class="table table-hover" id="orderTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Order ID</th>
                                <th>Full Name</th>
                                <th>Contact Number</th>
                                <th>Address</th>
                                <th>Mode of Payment</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Cash</th>
                                <th>Change</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($order = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_phonenum']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_address']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_mop']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_qty']); ?></td>
                                     <td><?php echo htmlspecialchars($order['order_total']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_cash']); ?></td>
                                     <td><?php echo htmlspecialchars($order['order_change']); ?></td>
                                    <td><?php echo htmlspecialchars($order['status_name']); ?></td>
                                    
                                    <td>
                                        <!-- Edit button trigger modal -->
                                        <a href="#" class="edit-icon" data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-oder-id="<?php echo htmlspecialchars($order['order_id']); ?>"
                                            data-full-name="<?php echo htmlspecialchars($order['order_fullname']); ?>"
                                            data-contact="<?php echo htmlspecialchars($order['order_phonenum']); ?>"
                                            data-address="<?php echo htmlspecialchars($order['order_address']); ?>"
                                            data-mop="<?php echo htmlspecialchars($order['order_mop']); ?>"
                                            data-qty="<?php echo htmlspecialchars($order['order_qty']); ?>"
                                            data-total="<?php echo htmlspecialchars($order['order_total']); ?>"
                                            data-cash="<?php echo htmlspecialchars($order['order_cash']); ?>"
                                            data-change="<?php echo htmlspecialchars($order['order_change']); ?>"
                                            data-status="<?php echo htmlspecialchars($order['status_name']); ?>">
                                            <i class="fa fa-edit"></i>
                                        </a>

                                        <!-- Delete button -->
                                        <a href="order_delete.php?id=<?php echo htmlspecialchars($order['order_id']); ?>" class="delete-icon" onclick="confirmDelete(event, '<?php echo htmlspecialchars($order['order_id']); ?>')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                 
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="11">No order found.</td></tr>
                            <?php endif; ?>
                            
                        </tbody>
                    </table>
                </div>
                       



           
             <!-- Edit Order Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form class="row g-3" id="editOrderForm" method="post" action="update_order.php">
          <input type="hidden" id="edit-order-id" name="order_id">

          <div class="col-md-4">
            <label for="edit-full-name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="edit-full-name" name="order_fullname" required>
          </div>

          <div class="col-md-3">
            <label for="edit-contact" class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="edit-contact" name="order_phonenum" required>
          </div>

          <div class="col-md-5">
            <label for="edit-address" class="form-label">Address</label>
            <input type="text" class="form-control" id="edit-address" name="order_address" required>
          </div>

          <div class="col-md-3">
            <label for="edit-mop" class="form-label">Mode of Payment</label>
            <input type="text" class="form-control" id="edit-mop" name="order_mop" required>
          </div>

          <div class="col-md-2">
            <label for="edit-qty" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="edit-qty" name="order_qty" required>
          </div>

          <div class="col-md-3">
            <label for="edit-total" class="form-label">Total Amount</label>
            <input type="number" class="form-control" id="edit-total" name="order_total" readonly>
          </div>

          <div class="col-md-3">
            <label for="edit-cash" class="form-label">Cash</label>
            <input type="number" class="form-control" id="edit-cash" name="order_cash" readonly>
          </div>

          <div class="col-md-3">
            <label for="edit-change" class="form-label">Change</label>
            <input type="number" class="form-control" id="edit-change" name="order_change" readonly>
          </div>

         <div class="col-md-3">
                        <label for="status_name">Order Status</label>
                        <select id="status_name" name="status_name" class="form-select" required>
                            <option value="">Select Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Completed">Processing</option>
                            <option value="Cancelled">Shipped</option>
                            <option value="Cancelled">Delivered</option>
                            <option value="Cancelled">Canceled</option>
                        </select>
                    </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
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
                <a class="page-link" href="?page=<?php echo $page - 1; ?>&status_code=<?php echo urlencode($status_code ?? ''); ?>&order_date=<?php echo urlencode($order_date ?? ''); ?>" aria-label="Previous">
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
                <a class="page-link" href="?page=<?php echo $i; ?>&status_code=<?php echo urlencode($status_code ?? ''); ?>&order_date=<?php echo urlencode($order_date ?? ''); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>

        <!-- Next Page Link -->
        <?php if ($page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>&status_code=<?php echo urlencode($status_code ?? ''); ?>&order_date=<?php echo urlencode($order_date ?? ''); ?>" aria-label="Next">
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

//sorting by calendar
//edit modal
document.addEventListener('DOMContentLoaded', function () {
        
        const editIcons = document.querySelectorAll('.edit-icon');
// Get the edit buttons
const editButtons = document.querySelectorAll('.edit-icon');

// Attach click event to all edit buttons
editButtons.forEach(button => {
    button.addEventListener('click', function() {
        // Get data from the button's data attributes
        const orderId = this.getAttribute('data-oder-id');
        const fullName = this.getAttribute('data-full-name');
        const contact = this.getAttribute('data-contact');
        const address = this.getAttribute('data-address');
        const mop = this.getAttribute('data-mop');
        const qty = this.getAttribute('data-qty');
        const total = this.getAttribute('data-total');
        const cash = this.getAttribute('data-cash');
        const change = this.getAttribute('data-change');
        const status = this.getAttribute('data-status');

        // Set the values in the modal form inputs
        document.getElementById('edit-order-id').value = orderId;
        document.getElementById('edit-full-name').value = fullName;
        document.getElementById('edit-contact').value = contact;
        document.getElementById('edit-address').value = address;
        document.getElementById('edit-mop').value = mop;
        document.getElementById('edit-qty').value = qty;
        document.getElementById('edit-total').value = total;
        document.getElementById('edit-cash').value = cash;
        document.getElementById('edit-change').value = change;
        document.getElementById('edit-status').value = status;
    });
});

    });

    //filter by order date
    
function filterOrdersByDate(page = 1) {
    const orderDate = document.getElementById('orderDate').value;

    var xhr = new XMLHttpRequest();
    const url = `filterOrderDate.php?page=${page}&order_date=${encodeURIComponent(orderDate)}`;

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

 // sort table by category
function sortTableStatus(page = 1) {
    var statusCode = document.getElementById('sort-status').value;

    if (statusCode === "") {
        location.reload();
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open("GET", "sortingStatus.php?status_code=" + statusCode + "&page=" + page, true);
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
        XLSX.writeFile(workbook, 'order_table.xlsx');
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

    doc.save('order_table.pdf');
});
// confirmation for deleting product
function confirmDelete(event, orderId) {
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
            window.location.href = 'order_delete.php?id=' + orderId;
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