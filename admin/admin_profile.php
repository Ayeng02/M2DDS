
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
.profile-card {
            max-width: 100%;
         font-family: Arial, sans-serif;
            margin: 50px auto;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            background-color: #fff;
        }
 .strength-meter {
            margin-top: 10px;
            height: 5px;
            width: 100%;
            border-radius: 3px;
        }
        .strength-meter-weak {
            background-color: red;
        }
        .strength-meter-medium {
            background-color: orange;
        }
        .strength-meter-strong {
            background-color: green;
        }
        #password-feedback {
            font-size: 18px;
            margin-top: 5px;
        }
        #password-feedback.weak {
            color: red;
            
        }
        #password-feedback.medium {
            color: orange;
        }
        #password-feedback.strong {
            color: green;
        }
         .input-group {
            position: relative;
        }
        .input-group-append {
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
        }
        label{
            margin-right: 10px;
        }
        .info-card p {
            margin: 10px 0;
            font-size: 20px;
            color: #333;
        }
         .info-card span {
            color: gray;
        }

        /* Bold and colorize field labels (e.g., "Full Name:") */
        .info-card strong {
            font-weight: 600;
            color: #555;
        }
        .text-center{
            color: #a72828;
            font-weight: bold;
            font-size: 40px;
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
        $admin_id = $_SESSION['admin_id'];
        $sql = "SELECT admin_name, admin_email, admin_num, admin_role FROM admin_tbl WHERE admin_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $stmt->bind_result($name,$email, $contact, $admin_role);
        $stmt->fetch();
        $stmt->close();
        $_SESSION['admin_role'] = $admin_role;

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
ob_end_flush(); 
?>
 <div class="container">
        <div class="profile-card">
            <div class="info-card">
            <h2 class="text-center">Admin Profile</h2>
            <p><strong>Full Name:</strong><span> <?php echo htmlspecialchars($name); ?> </span></p>
            <p><strong>Email:</strong><span id="email"> <?php echo htmlspecialchars($email); ?> </span></p>
            <p><strong>Contact:</strong><span> <?php echo htmlspecialchars($contact); ?> </span></p>
            </div>
            <!-- Check if the logged-in admin is a super admin -->
        <?php if ($_SESSION['admin_role'] === 'super_admin'): ?>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                <i class="fas fa-plus"></i> Add Admin
            </button>
        <?php else: ?>
            <button type="button" class="btn btn-success" disabled>
                <i class="fas fa-plus"></i> Add Admin
            </button>
        <?php endif; ?>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#editAdminModal">
                <i class="fa fa-edit"></i> Edit Info
            </button>
             <button type="button" class="btn btn-primary" onclick="window.location.href='changepass.php';">
          
                <i class="fa fa-lock"></i> Change Password
                
            </button>
        
        
           
        </div>
  
         <div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAdminModalLabel">Add New Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add Admin Form -->
                    <form method="post" action="addAdmin.php">
                        <div class="mb-3">
                            <label for="admin_fname" class="form-label">Full Name</label>
                            <input type="text" name="admin_name" id="admin_fname" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="admin_email" class="form-label">Email</label>
                            <input type="email" name="admin_email" id="admin_email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="admin_contact" class="form-label">Contact Number</label>
                            <input type="text" name="admin_num" id="admin_contact" class="form-control" required>
                        </div>
                         
                        <div class="mb-3">
                            <label for="admin_password" class="form-label">Password</label>
                             <div class="input-group">
                                <input type="password" id="password" name="admin_pass" class="form-control" required>
                                <div class="input-group-append">
                                    <i id="toggleAddPassword" class="fas fa-eye"></i>
                                </div>
                                
                            </div>
                            <div id="password-feedback" class="mt-2"></div>
                                <div id="strength-meter" class="strength-meter"></div>
                        </div>
                        <button type="submit" class="btn btn-success">Add Admin</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
   <div class="modal fade" id="editAdminModal" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAdminModalLabel">Edit Admin Info</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="updateAdminInfo.php" method="POST">
                <div class="modal-body">
                    <!-- Full Name Field -->
                    <div class="mb-3">
                        <label for="editFullName" class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" id="editFullName" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" id="editEmail" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <!-- Contact Field -->
                    <div class="mb-3">
                        <label for="editContact" class="form-label">Contact</label>
                        <input type="text" name="contact" class="form-control" id="editContact" value="<?php echo htmlspecialchars($contact); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                        title: 'Admin Added',
                        text: '" . $_SESSION['success'] . "',
                        confirmButtonText: 'OK'
                    });
                });
            </script>";
            unset($_SESSION['success']);
        }
        ?>
   
    </div>
    <!-- /#page-content-wrapper -->
</div>
</div>
<!-- /#wrapper -->

<!-- Bootstrap and JavaScript -->
 <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
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

</script>
 <script>
document.getElementById("toggleAddPassword").addEventListener("click", function () {
    const addPasswordField = document.getElementById("password");
    const type = addPasswordField.type === "password" ? "text" : "password";
    addPasswordField.type = type;
    this.classList.toggle("fa-eye-slash");
});

    </script>
</body>
</html>