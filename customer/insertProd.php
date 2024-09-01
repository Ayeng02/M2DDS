<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Product</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2>Insert Product</h2>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $category_code = $_POST['category_code'];
        $prod_name = $_POST['prod_name'];
        $prod_desc = $_POST['prod_desc'];
        $prod_price = $_POST['prod_price'];
        $prod_discount = isset($_POST['prod_discount']) ? $_POST['prod_discount'] : 0;
        $prod_qoh = $_POST['prod_qoh'];
        $prod_img = $_FILES['prod_img'];

        // Check if the image size is within the limit
        if ($prod_img['size'] > 5242880) { // 5MB in bytes
            echo "<div class='alert alert-danger'>The image size should not exceed 5MB.</div>";
        } else {
            // Define the upload directory
            $upload_dir = 'Product-Images/';
            
            // Create the directory if it does not exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Define the target file path
            $target_file = $upload_dir . basename($prod_img['name']);
            
            // Move the uploaded file to the target directory
            if (!move_uploaded_file($prod_img['tmp_name'], $target_file)) {
                echo "<div class='alert alert-danger'>There was an error uploading the file.</div>";
            } else {
                // Database connection
                $conn = new mysqli('localhost', 'root', '', 'm2dds');
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Call the stored procedure to insert the product
                $stmt = $conn->prepare("CALL sp_InsertProduct(?, ?, ?, ?, ?, ?, ?)");
                if ($stmt === false) {
                    die('Prepare failed: ' . htmlspecialchars($conn->error));
                }

                $stmt->bind_param('ssssdis', $category_code, $prod_name, $prod_desc, $prod_price, $prod_discount, $prod_qoh, $target_file);
                if (!$stmt->execute()) {
                    die('Execute failed: ' . htmlspecialchars($stmt->error));
                }

                echo "<div class='alert alert-success'>Product inserted successfully.</div>";

                $stmt->close();
                $conn->close();
            }
        }
    }

    // Fetch categories from the database
    $conn = new mysqli('localhost', 'root', '', 'm2dds');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $categories = $conn->query("SELECT category_code, category_name FROM category_tbl");

    if ($categories === false) {
        die('Query failed: ' . htmlspecialchars($conn->error));
    }
    ?>
    <form id="insertProductForm" action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="category_code">Category</label>
            <select class="form-control" id="category_code" name="category_code" required>
                <option value="">Select a category</option>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['category_code']); ?>">
                        <?php echo htmlspecialchars($row['category_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="prod_name">Product Name</label>
            <input type="text" class="form-control" id="prod_name" name="prod_name" required>
        </div>
        <div class="form-group">
            <label for="prod_desc">Product Description</label>
            <textarea class="form-control" id="prod_desc" name="prod_desc" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="prod_price">Product Price</label>
            <input type="number" step="0.01" class="form-control" id="prod_price" name="prod_price" required>
        </div>
        <div class="form-group">
            <label for="prod_discount">Product Discount</label>
            <input type="number" step="0.01" class="form-control" id="prod_discount" name="prod_discount" value="0">
            <small class="form-text text-muted">Leave as 0 if no discount.</small>
        </div>
        <div class="form-group">
            <label for="prod_qoh">Quantity on Hand</label>
            <input type="number" class="form-control" id="prod_qoh" name="prod_qoh" required>
        </div>
        <div class="form-group">
            <label for="prod_img">Product Image</label>
            <input type="file" class="form-control-file" id="prod_img" name="prod_img" required>
            <small class="form-text text-muted">Max size: 5MB.</small>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<script>
$(document).ready(function() {
    $('#insertProductForm').on('submit', function(e) {
        const fileInput = $('#prod_img')[0];
        const file = fileInput.files[0];

        if (file.size > 5242880) { // 5MB in bytes
            alert('The image size should not exceed 5MB.');
            e.preventDefault();
        }
    });
});
</script>

</body>
</html>
