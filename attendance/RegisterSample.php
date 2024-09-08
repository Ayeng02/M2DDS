<?php
include '../includes/db_connect.php'; // Include database connection

// Function to generate emp_id in format EMP[year][id]
function generateEmpID($pdo) {
    $year = date("Y");
    // Fetch the last ID number from the database for the current year
    $stmt = $pdo->query("SELECT MAX(SUBSTRING(emp_id, 8, 4)) AS last_id FROM emp_tbl WHERE emp_id LIKE 'EMP$year%'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $lastID = $result['last_id'] ?? 0;
    $nextID = str_pad($lastID + 1, 4, '0', STR_PAD_LEFT);
    return "EMP" . $year . $nextID;
}

// Initialize PDO connection
$pdo = new PDO("mysql:host=localhost;dbname=m2dds", "root", ""); // Add your database credentials
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Generate a new emp_id before the form is submitted
$emp_id = generateEmpID($pdo);

// Check if the form was submitted
// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the emp_id from the form POST data
    $emp_id = $_POST['emp_id']; // Get emp_id from the form
    if (empty($emp_id)) {
        echo "Employee ID is missing.";
        exit();
    }

    // Get other form values
    $emp_fname = htmlspecialchars($_POST['emp_fname']);
    $emp_lname = htmlspecialchars($_POST['emp_lname']);
    $emp_email = filter_var($_POST['emp_email'], FILTER_SANITIZE_EMAIL);
    $emp_num = htmlspecialchars($_POST['emp_num']);
    $emp_address = htmlspecialchars($_POST['emp_address']);
    $emp_role = htmlspecialchars($_POST['emp_role']);
    $emp_pass = password_hash($_POST['emp_pass'], PASSWORD_DEFAULT); // Hash the password
    $emp_img = $_FILES['emp_img']['name'];
    $created_at = date('Y-m-d H:i:s');

    // Handle image upload (image validation and processing here)
    // ...

    // If the upload is successful, insert the data into the database
    $stmt = $pdo->prepare("INSERT INTO emp_tbl (emp_id, emp_fname, emp_lname, emp_email, emp_num, emp_address, emp_role, emp_pass, emp_img, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$emp_id, $emp_fname, $emp_lname, $emp_email, $emp_num, $emp_address, $emp_role, $emp_pass, $emp_img, $created_at])) {
        header("Location: ResultSample.php");
        exit();
    } else {
        echo "Failed to save data.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 500px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .container h2 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .form-group input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .form-group input[type="submit"]:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Employee Registration</h2>
    <form action="RegisterSample.php" method="POST" enctype="multipart/form-data">
        
        <!-- Employee ID (displayed but not editable) -->
        <!-- Employee ID (displayed but not editable) -->
        <div class="form-group">
            <label for="emp_id">Employee ID</label>
            <input type="text" name="emp_id" id="emp_id" value="<?php echo $emp_id; ?>" readonly>
        </div>


        <!-- First Name -->
        <div class="form-group">
            <label for="emp_fname">First Name</label>
            <input type="text" name="emp_fname" id="emp_fname" required>
        </div>

        <!-- Last Name -->
        <div class="form-group">
            <label for="emp_lname">Last Name</label>
            <input type="text" name="emp_lname" id="emp_lname" required>
        </div>

        <!-- Email -->
        <div class="form-group">
            <label for="emp_email">Email (Google Account)</label>
            <input type="email" name="emp_email" id="emp_email" required pattern=".+@gmail\.com" title="Please enter a valid Google email">
        </div>

        <!-- Phone Number -->
        <div class="form-group">
            <label for="emp_num">Phone Number</label>
            <input type="text" name="emp_num" id="emp_num" required pattern="[0-9]{11}" title="Please enter a valid 11-digit phone number">
        </div>

        <!-- Address -->
        <div class="form-group">
            <label for="emp_address">Address</label>
            <input type="text" name="emp_address" id="emp_address" required>
        </div>

        <!-- Role -->
        <div class="form-group">
            <label for="emp_role">Role</label>
            <select name="emp_role" id="emp_role" required>
                <option value="cashier">Cashier</option>
                <option value="shipper">Shipper</option>
                <option value="butcher">Butcher</option>
                <option value="order_manager">Order Manager</option>
            </select>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="emp_pass">Password</label>
            <input type="password" name="emp_pass" id="emp_pass" required>
        </div>

        <!-- Image -->
        <div class="form-group">
            <label for="emp_img">Upload Image</label>
            <input type="file" name="emp_img" id="emp_img" accept="image/*" required>
        </div>

        <!-- Submit Button -->
        <div class="form-group">
            <input type="submit" value="Register">
        </div>
    </form>
</div>

</body>
</html>
