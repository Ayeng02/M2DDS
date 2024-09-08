<?php
include '../includes/db_connect.php'; // Include database connection

// Initialize PDO connection
$pdo = new PDO("mysql:host=localhost;dbname=m2dds", "root", ""); // Add your database credentials
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch the latest registered employee (assuming you want to display the most recently registered)
$stmt = $pdo->query("SELECT * FROM emp_tbl ORDER BY created_at DESC LIMIT 1");
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    echo "No employee data found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Registration Result</title>
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
        .form-group p {
            margin: 0;
        }
        .form-group img {
            max-width: 150px;
            height: auto;
            display: block;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Employee Registration Result</h2>

    <div class="form-group">
        <label for="emp_id">Employee ID</label>
        <p id="emp_id"><?php echo htmlspecialchars($employee['emp_id']); ?></p>
    </div>

    <div class="form-group">
        <label for="emp_fname">First Name</label>
        <p id="emp_fname"><?php echo htmlspecialchars($employee['emp_fname']); ?></p>
    </div>

    <div class="form-group">
        <label for="emp_lname">Last Name</label>
        <p id="emp_lname"><?php echo htmlspecialchars($employee['emp_lname']); ?></p>
    </div>

    <div class="form-group">
        <label for="emp_email">Email</label>
        <p id="emp_email"><?php echo htmlspecialchars($employee['emp_email']); ?></p>
    </div>

    <div class="form-group">
        <label for="emp_num">Phone Number</label>
        <p id="emp_num"><?php echo htmlspecialchars($employee['emp_num']); ?></p>
    </div>

    <div class="form-group">
        <label for="emp_address">Address</label>
        <p id="emp_address"><?php echo htmlspecialchars($employee['emp_address']); ?></p>
    </div>

    <div class="form-group">
        <label for="emp_role">Role</label>
        <p id="emp_role"><?php echo htmlspecialchars($employee['emp_role']); ?></p>
    </div>

    <div class="form-group">
        <label for="emp_img">Image</label>
        <img src="uploads/<?php echo htmlspecialchars($employee['emp_img']); ?>" alt="Employee Image">
    </div>

    <div class="form-group">
        <label for="created_at">Created At</label>
        <p id="created_at"><?php echo htmlspecialchars($employee['created_at']); ?></p>
    </div>

</div>

</body>
</html>
