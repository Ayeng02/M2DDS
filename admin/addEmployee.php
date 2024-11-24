
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



.employee-table-container {
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
.combo-box label, .combo-box select {
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
     <?php

// Include PHPMailer library files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require '../PHPMailer/PHPMailer/src/Exception.php';
require '../PHPMailer/PHPMailer/src/PHPMailer.php';
require '../PHPMailer/PHPMailer/src/SMTP.php';
// Helper functions for validation
function validatePhoneNumber($phone) {
    return preg_match('/^(09\d{9})$/', $phone); // Philippine format
}

function validateAddress($address) {
    return preg_match('/^[\w\s\.,\-#]+, [\w\s]+, [\w\s]+$/', $address); // Simple address validation
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL); // Basic email validation
}

function validateName($name) {
    return preg_match('/^[A-ZÑ][a-zA-ZñÑ\s]*$/', $name); // Starts with a capital letter (including Ñ), allows letters and ñ
}

$error = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $emp_fname = trim($_POST['emp_fname']);
    $emp_lname = trim($_POST['emp_lname']);
    $emp_email = trim($_POST['emp_email']);
    $emp_num = trim($_POST['emp_num']);
    $emp_address = trim($_POST['emp_address']);
    $emp_role = trim($_POST['emp_role']);
    $emp_pass = $_POST['emp_pass'];
    $emp_img = $_FILES['emp_img'];

    // Validate input fields
    if (!empty($emp_fname) && !empty($emp_lname) && !empty($emp_email) && !empty($emp_num) && !empty($emp_address) && !empty($emp_role) && !empty($emp_pass) && !empty($emp_img['name'])) {
        if (validateName($emp_fname) && validateName($emp_lname)) {
            if (validateEmail($emp_email)) {
                if (validatePhoneNumber($emp_num)) {
                    if (validateAddress($emp_address)) {
                        // Check for numeric restrictions on emp_num
                        if (strlen($emp_num) <= 11 && ctype_digit($emp_num)) {
                            // Check if email already exists
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM emp_tbl WHERE emp_email = ?");
                            $stmt->bind_param("s", $emp_email);
                            $stmt->execute();
                            $stmt->bind_result($count);
                            $stmt->fetch();
                            $stmt->close();

                            if ($count == 0) {
                                // Process image upload
                                $target_dir = "../employee_images/";
                                if (!is_dir($target_dir)) {
                                    mkdir($target_dir, 0777, true);
                                }
                                $target_file = $target_dir . basename($emp_img['name']);
                                $relative_path_to_store = "employee_images/" . basename($emp_img['name']);

                                if (move_uploaded_file($emp_img['tmp_name'], $target_file)) {
                                    // Hash the password for security
                                    $hashed_pass = password_hash($emp_pass, PASSWORD_DEFAULT);

                                    // Insert the employee record into the database
                                    if ($stmt = $conn->prepare("CALL sp_insertEmployee (?,?,?,?,?,?,?,?)")) {
                                        $stmt->bind_param("ssssssss", $emp_fname, $emp_lname, $emp_email, $emp_num, $emp_address, $emp_role, $hashed_pass, $relative_path_to_store);

                                        if ($stmt->execute()) {

                                            // Insert into system log
                                            $user_id = $_SESSION['admin_id']; 
                                            $action = "Added new employee: " . $emp_fname . " " . $emp_lname;

                                            $log_stmt = $conn->prepare("INSERT INTO systemlog_tbl (user_id, user_type, systemlog_action, systemlog_date) VALUES (?, ?, ?, NOW())");
                                            $user_type = 'Admin';
                                            $log_stmt->bind_param("sss", $user_id, $user_type, $action);
                                            $log_stmt->execute();
                                            $log_stmt->close();

                                            $_SESSION['alert'] = [
                                                'icon' => 'success',
                                                'title' => 'New employee added successfully.'
                                            ];
                                            
                                            
                                           // Email credentials to the employee
                                            $mail = new PHPMailer(true);

                                            try {
                                                // Server settings
                                                $mail->isSMTP();
                                                $mail->SMTPDebug  = 2; // Enable verbose debug output
                                                $mail->Host       = 'smtp.gmail.com';
                                                $mail->SMTPAuth   = true;                
                                                $mail->Username   = 'arieldohinogbusiness@gmail.com';
                                                $mail->Password   = 'lystrtavajrupmnq';  // App password, if 2FA is enabled
                                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                                $mail->Port       = 587;

                                                // Recipients
                                                $mail->setFrom('no-reply@yourdomain.com', "Melos's Meatshop");
                                                $mail->addAddress($emp_email, $emp_fname . ' ' . $emp_lname);

                                                // Email content
                                                $mail->isHTML(true);
                                                $mail->Subject = "Welcome to the Melos's Meatshop";
                                                $mail->Body    = "
                                                    <h3>Welcome, {$emp_fname} {$emp_lname}</h3>
                                                    <p>Your account has been created. Here are your credentials:</p>
                                                    <ul>
                                                        <li><strong>Email:</strong> {$emp_email}</li>
                                                        <li><strong>Password:</strong> {$emp_pass}</li>
                                                    </ul>
                                                    <p>Please change your password after logging in for the first time.</p>
                                                    <p>Regards,<br>Melos's Meatshop</p>";
                                                
                                                $mail->send();
                                                echo 'Message has been sent';
                                            } catch (Exception $e) {
                                                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                                            }
                                            $success = true;
                                        } else {
                                            $error = 'Database error: ' . htmlspecialchars($stmt->error);
                                        }
                                        $stmt->close();
                                    }
                                } else {
                                    $error = 'Failed to upload image.';
                                }
                            } else {
                                $error = 'Email already exists.';
                            }
                        } else {
                            $error = 'Contact number must be numeric and up to 11 digits.';
                        }
                    } else {
                        $error = 'Address format is invalid.';
                    }
                } else {
                    $error = 'Phone number must be in Philippine cellular format (09xxxxxxxxx).';
                }
            } else {
                $error = 'Invalid email format.';
            }
        } else {
            $error = 'First name and last name must start with a capital letter and contain only letters.';
        }
    } else {
        $error = 'All fields are required.';
    }

    // Redirect to prevent form resubmission
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



      <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="employeeModalLabel">Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" action="" method="post" enctype="multipart/form-data">
                    <div class="col-md-4">
                        <label for="emp_fnam">First Name</label>
                        <input type="text" class="form-control" id="emp_fname" name="emp_fname" placeholder="Enter Firstname" required>
                    </div>
                    <div class="col-md-4">
                        <label for="emp_lname">Last Name</label>
                        <input type="text" class="form-control" id="emp_lname" name="emp_lname" placeholder="Enter Lastname" required>
                    </div>
                    <div class="col-md-4">
                        <label for="emp_email">Email</label>
                        <input type="email" class="form-control" id="emp_email" name="emp_email" placeholder="Enter Email" required>
                    </div>
                    <div class="col-md-4">
                        <label for="emp_num">Contact Number</label>
                        <input type="text" class="form-control" id="emp_num" name="emp_num" placeholder="Enter Contact Number" required maxlength="11" pattern="\d{1,11}">
                    </div>
                    <div class="col-md-5">
                        <label for="emp_address">Address</label>
                        <textarea class="form-control" id="emp_address" name="emp_address" rows="1" placeholder="Purok Barangay, City, Province" required></textarea>
                    </div>
                    <div class="col-md-3">
                        <label for="emp_role">Role</label>
                        <select class="form-select" id="emp_role" name="emp_role" required>
                            <option value="">Select a role</option>
                            <option value="Order Manager">Order Manager</option>
                            <option value="Shipper">Shipper</option>
                            <option value="Butcher">Butcher</option>
                            <option value="Cashier">Cashier</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="emp_pass">Password</label>
                        <input type="password" class="form-control" id="emp_pass" placeholder="Enter Password Ex. @Sample1234" name="emp_pass" required>
                    </div>
                    <div class="col-md-4">
                        <label for="emp_img">Employee Image</label>
                        <input type="file" class="form-control-file" id="emp_img" name="emp_img" accept="image/*">
                        <img id="image_preview" src="" alt="Image Preview" style="display: none; margin-top: 10px; width: 50%; height: 200px;">
                    </div>
                    <div class="d-grid gap-2 col-5 mx-auto">
                    <button type="submit" class="btn btn-primary btn-lg">ADD</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

        
             <div id="header-table-title">Employee</div>
             <div class="d-grid gap-2 col-2 mx-auto">
            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#employeeModal">
            Add Employee
            </button>
                        </div>
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
            <div class="employee-table-container">
               <div class="combo-box">
                <label for="sort">Sort by Employee Name: </label>
                <select id="sort-name" onchange="sortTable()">
                    <option value="a-z">A-Z</option>
                    <option value="z-a">Z-A</option>
                </select>
                <label for="sort-role">Sort by Employee Role: </label>
                <select id="sort-role" onchange="sortTableByRole()">
                    <option value="all">All</option>
                    <option value="Shipper">Shipper</option>
                    <option value="Butcher">Butcher</option>
                    <option value="Cashier">Cashier</option>
                    <option value="Order Manager">Order Manager</option>
                </select>
            </div>
               <?php
                   
                    $limit = 7;

                    
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $offset = ($page - 1) * $limit;

                    // Count the total number of employees
                    $count_sql = "SELECT COUNT(*) AS total FROM emp_tbl";
                    $count_result = $conn->query($count_sql);
                    $total_rows = $count_result->fetch_assoc()['total'];
                    $total_pages = ceil($total_rows / $limit);

                    // Fetch employees for the current page
                    $sql = "SELECT emp_id, emp_fname, emp_lname, emp_email, emp_num, emp_address, emp_role, emp_img 
                            FROM emp_tbl 
                            ORDER BY emp_id DESC 
                            LIMIT $limit OFFSET $offset";
                    $result = $conn->query($sql);
                    ?>

                <div class="table-responsive">
                    <table class="table table-hover" id="employeeTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Employee ID</th>
                                 <th>Image</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                <th>Address</th>
                                <th>Role</th>
                               
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($employee = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($employee['emp_id']); ?></td>
                                    <td>
                                        <?php if (!empty($employee['emp_img'])): ?>
                                            <img src="../<?php echo htmlspecialchars($employee['emp_img']); ?>" alt="Employee Image" style="width: 55px; height: 55px; border-radius: 5px;">
                                        <?php else: ?>
                                            No Image
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($employee['emp_fname'] . ' ' . $employee['emp_lname']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['emp_email']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['emp_num']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['emp_address']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['emp_role']); ?></td>
                                    
                                    <td>
                                        <!-- Edit button trigger modal -->
                                        <a href="#" class="edit-icon" data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-emp-id="<?php echo htmlspecialchars($employee['emp_id']); ?>"
                                            data-full-name="<?php echo htmlspecialchars($employee['emp_fname'] . ' ' . $employee['emp_lname']); ?>"
                                            data-email="<?php echo htmlspecialchars($employee['emp_email']); ?>"
                                            data-contact="<?php echo htmlspecialchars($employee['emp_num']); ?>"
                                            data-address="<?php echo htmlspecialchars($employee['emp_address']); ?>"
                                            data-role="<?php echo htmlspecialchars($employee['emp_role']); ?>"
                                            data-emp-img="<?php echo htmlspecialchars($employee['emp_img']); ?>">
                                            <i class="fa fa-edit"></i>
                                        </a>

                                        <!-- Delete button -->
                                        <a href="delete_Employee.php?id=<?php echo htmlspecialchars($employee['emp_id']); ?>" class="delete-icon" onclick="confirmDelete(event, '<?php echo htmlspecialchars($employee['emp_id']); ?>')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                 
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="8">No employees found.</td></tr>
                            <?php endif; ?>
                            
                        </tbody>
                    </table>
                </div>
                       



            <!-- Edit Employee Modal -->
                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editEmployeeForm" action="edit_employee.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                          <label for="ID" class="form-label">Employee ID</label>
                        <input type="text" class="form-control" name="emp_id" id="editEmp-id" readonly>

                        <div class="mb-3">
                            <label for="full-name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="editFull-name" name="full_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="editContact" name="contact" required>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="editAddress" name="address" required>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="editRole" name="role" required>
                            <option value="Shipper">Shipper</option>
                            <option value="Butcher">Butcher</option>
                            <option value="Cashier">Cashier</option>
                            <option value="Order Manager">Order Manager</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="emp-img" class="form-label">Employee Image</label>
                            <input type="file" class="form-control" id="emp-img" name="emp_img" accept="image/*">
                            <img id="Current-emp-img" src="../" alt="Employee Image" style="width: 50%; height: 150px;" class="mt-2">
                        </div>
                        </div>

                        <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
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
                        title: 'Employee Updated',
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


//edit modal
document.addEventListener('DOMContentLoaded', function () {
        
        const editIcons = document.querySelectorAll('.edit-icon');

        editIcons.forEach( icon => {
            icon.addEventListener('click', function () {
                // Get the data attributes from the clicked edit icon
                const empId = this.getAttribute('data-emp-id');
                const fullName = this.getAttribute('data-full-name');
                const email = this.getAttribute('data-email');
                const contact = this.getAttribute('data-contact');
                const address = this.getAttribute('data-address');
                const role = this.getAttribute('data-role');
                const empImg = this.getAttribute('data-emp-img');

                // Populate the modal form with the employee's data
                document.getElementById('editEmp-id').value = empId;
                document.getElementById('editFull-name').value = fullName;
                document.getElementById('editEmail').value = email;
                document.getElementById('editContact').value = contact;
                document.getElementById('editAddress').value = address;
                document.getElementById('editRole').value = role;
                
                // Show current employee image in the modal
                document.getElementById('Current-emp-img').src = "../" + empImg // Set current image path
            });
        });
    });
//show image before adding 
document.getElementById('emp_img').addEventListener('change', function (e) {
        const imagePreview = document.getElementById('image_preview');
        const file = e.target.files[0];
        const reader = new FileReader();
        
        reader.onload = function (event) {
            imagePreview.src = event.target.result;
            imagePreview.style.display = 'block';
        }
        
        if (file) {
            reader.readAsDataURL(file);
        }
    });
//copy in clipboard
document.getElementById('copyTableBtn').addEventListener('click', function() {
    var table = document.getElementById('employeeTable');
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
        const table = document.getElementById('employeeTable');
        const workbook = XLSX.utils.table_to_book(table, { sheet: "Products" });
        XLSX.writeFile(workbook, 'employee_table.xlsx');
    });
//Download as pdf
document.getElementById('downloadPDF').addEventListener('click', function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.autoTable({
        html: '#employeeTable',
        startY: 20,
        theme: 'grid',
        headStyles: { fillColor: [0, 150, 0] },  // Custom header color
        margin: { top: 10 },
    });

    doc.save('Employee_table.pdf');
});
// confirmation for deleting product
function confirmDelete(event, empId) {
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
            window.location.href = 'delete_Employee.php?id=' + empId;
        }
    });
}

//sort table by role

function sortTableByRole() {
    const select = document.getElementById('sort-role');
    const role = select.value;
    const table = document.getElementById('employeeTable');
    const rows = Array.from(table.getElementsByTagName('tr')).slice(1); // Skip header row

    rows.forEach(row => {
        const roleCell = row.cells[6]; // Assuming the role is in the 7th column
        const rowRole = roleCell.textContent || roleCell.innerText;

        if (role === 'all' || rowRole === role) {
            row.style.display = ''; // Show row
        } else {
            row.style.display = 'none'; // Hide row
        }
    });

    
}


// sort table by Name
function sortTable() {
    var table, rows, switching, i, x, y, shouldSwitch;
    table = document.getElementById("employeeTable");
    switching = true;

    var sortOption = document.getElementById("sort-name").value;
    
    while (switching) {
        switching = false;
        rows = table.rows;
        
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            
            
            x = rows[i].getElementsByTagName("TD")[2]; 
            y = rows[i + 1].getElementsByTagName("TD")[2];
            
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