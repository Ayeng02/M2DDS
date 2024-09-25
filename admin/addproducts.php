
<?php 
ob_start();
session_start();
include '../includes/db_connect.php';
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

.addprod-content{
    display: flex;
    border: 1px solid #8c1c1c;
    width: 40%;
    height: 20vh;
    border-radius: 10px;
    margin-left: 30%;
    margin-top: 5rem;
    text-align: center;
    justify-content: center;
    align-items: center;
    font-size: 40px;
    font-weight: bold;
    color: #8c1c1c;
    background-color: #FF8225;
      text-shadow: 0 2px 7px #a72828;

}

.update-btn{
    display: flex;
    float: right;
    margin-right: 15rem;
    margin-top: 20px;
    width: 150px;
    height: 35px;
    justify-content: center;
    text-align: center;
    align-items: center;
    font-size: 20px;
    background-color: #007bff;
    border-radius: 5px;
    color: white;
}
.update-btn:hover{
    background-color: #0056b3;
}
.product-table-container {
    display: flex;
    flex-direction: row;
    width: 90%;
    height: 65vh; 
    margin-top: 10px;
    margin-left: 5%;
    padding-top: 20px;
    border-radius: 10px;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    flex-direction: column;
    text-align: center;
    
}

.product-table {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: auto;

}

.product-table h2 {
    margin-bottom: 10px;
    color: #007bff;
    text-align: center;
}
.product-table span{
    font-size: 30px;
}

.product-table table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed; 
}

.product-table thead {
    background-color: #007bff;
    color: white;
    font-size: 16px;
    text-transform: uppercase;
    text-align: center;
      position: sticky;
      
}

.product-table tbody {
    display: block;
    overflow-y: auto; 
    height: calc(100% - 45px);
}

.product-table thead, .product-table tbody tr {
    display: table;
    width: 100%; 
    table-layout: fixed; 
}

.product-table th, .product-table td {
    padding: 12px;
    border: 1px solid #dee2e6;
    text-align: center;
    white-space: nowrap;
    font-size: 16px;
}

.product-table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

.product-table tbody tr:hover {
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
   #copyToClipboard{
    margin-left: 5%;
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
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_code = $_POST['category_code'];
    $prod_name = $_POST['prod_name'];
    $prod_desc = $_POST['prod_desc'];
    $prod_price = $_POST['prod_price'];
    $prod_discount = isset($_POST['prod_discount']) ? $_POST['prod_discount'] : 0;
    $prod_qoh = $_POST['prod_qoh'];
    $prod_img = $_FILES['prod_img'];

    // Check if the image size is within the limit (5MB)
    if ($prod_img['size'] > 5242880) { // 5MB in bytes
        $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => 'The image size should not exceed 5MB.'
        ];
    } else {
        // Define the upload directory
        $upload_dir = '../Product-Images/';
        
        // Create the directory if it does not exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Define the target file path
        $target_file = $upload_dir . basename($prod_img['name']);

        // Save only the relative path in the database
        $relative_path_to_store = "Product-Images/" . basename($prod_img['name']);
        
        // Move the uploaded file to the target directory
        if (!move_uploaded_file($prod_img['tmp_name'], $target_file)) {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'There was an error uploading the file.'
            ];
        } else {
            // Database connection
            $conn = new mysqli('localhost', 'root', '', 'm2dds');
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Call the stored procedure to insert the product
            if ($stmt = $conn->prepare("CALL sp_InsertProduct(?, ?, ?, ?, ?, ?, ?)")) {
                $stmt->bind_param('ssssdis', $category_code, $prod_name, $prod_desc, $prod_price, $prod_discount, $prod_qoh,  $relative_path_to_store);

                if ($stmt->execute()) {
                    $_SESSION['alert'] = [
                        'icon' => 'success',
                        'title' => 'Product inserted successfully.'
                    ];
                } else {
                    $_SESSION['alert'] = [
                        'icon' => 'error',
                        'title' => 'Database error: ' . htmlspecialchars($stmt->error)
                    ];
                }

                $stmt->close();
            } else {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Failed to prepare the SQL statement.'
                ];
            }

            $conn->close();
        }
    }

    // Redirect to the same page after processing
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
ob_end_flush(); 
?>
<?php
    // Fetch categories from the database


    $categories = $conn->query("SELECT category_code, category_name FROM category_tbl");

    if ($categories === false) {
        die('Query failed: ' . htmlspecialchars($conn->error));
    }
    ?>
        <div class="container-fluid">
            <div class="content-header">
                
            </div>
             
             <div class="modal fade" id="insertProductModal" tabindex="-1" aria-labelledby="insertProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="insertProductModalLabel">Insert Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="insertProductForm" action="" method="POST" enctype="multipart/form-data">
                        <div class="col-md-3">
                            <label for="category_code">Category</label>
                            <select id="inputState" name="category_code" class="form-select" required>
                                <option value="">Select a category</option>
                                <?php while ($row = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($row['category_code']); ?>">
                                        <?php echo htmlspecialchars($row['category_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="prod_name">Product Name</label>
                            <input type="text" class="form-control" id="prod_name" name="prod_name" required>
                        </div>
                        <div class="col-md-2">
                            <label for="prod_price">Product Price</label>
                            <input type="number" step="0.01" class="form-control" id="prod_price" name="prod_price" required  min="1">
                        </div>
                        <div class="col-md-3">
                            <label for="prod_discount">Product Discount</label>
                            <input type="number" step="0.01" class="form-control" id="prod_discount" name="prod_discount" value="0">
                            <small class="form-text text-muted">Leave as 0 if no discount.</small>
                        </div>
                        <div class="col-md-4">
                            <label for="prod_qoh">Quantity on Hand</label>
                            <input type="number" class="form-control" id="prod_qoh" name="prod_qoh" required min="1">
                        </div>
                        <div class="col-md-4">
                            <img id="img_preview" class="col-md-15" src="" alt="Image Preview" style="display: none; margin-top: 10px; max-width: 100%; height: auto;">
                            <label for="prod_img">Product Image</label>
                            <input type="file" class="form-control-file" id="prod_img" name="prod_img" required accept="image/*">
                            <small class="form-text text-muted">Max size: 5MB.</small>
                        </div>
                        <div class="col-sm-10">
                            <label for="prod_desc">Product Description</label>
                            <textarea class="form-control" id="prod_desc" name="prod_desc" rows="3" required></textarea>
                        </div>
                        <div class="d-grid gap-2 col-5 mx-auto">
                            <button type="submit" class="btn btn-primary btn-lg">Add Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
            </a>
          
            <?php 
          $sql = "SELECT p.prod_code, c.category_name, p.prod_name, p.prod_price, p.prod_discount, p.prod_qoh, p.prod_img 
        FROM product_tbl p 
        JOIN category_tbl c ON p.category_code = c.category_code";
            $result = $conn->query($sql);

             $products = [];

             if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                 $products[] = array(
                     "prod_code" => $row['prod_code'],
                     "category_name" => $row['category_name'],
                    "prod_name" => $row['prod_name'],
                    "prod_price" => $row['prod_price'],
                    "prod_qoh" => $row['prod_qoh'],
                    "prod_discount" => $row['prod_discount'],
                    "prod_img" => $row['prod_img']
                );
            }
        } else {
            $products = [];
        }
            ?>
            <?php 
           $category_query = "SELECT DISTINCT category_name, category_code FROM category_tbl";
            $category_result = mysqli_query($conn, $category_query);
            ?>
             <div id="header-table-title">All Products</div>
             <div class="d-grid gap-2 col-2 mx-auto">
             <button type="button" class="btn btn-primary" 
              data-bs-toggle="modal" data-bs-target="#insertProductModal">
                 Add Product
            </button>
            </div>
            
            <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    
             <div class="modal-dialog">
             <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="successModalLabel">Copied</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Table copied to clipboard!
            </div>
            </div>
            </div>
            </div>

            <button id="copyToClipboard" class="btn btn-info">Copy to Clipboard</button>
             <button id="downloadPDF" class="btn btn-danger">Download as PDF</button>
              <button id="downloadExcel" class="btn btn-success">Download as Excel</button>
            <div class="product-table-container">
               <div class="combo-box">
        <label for="sort">Sort by Product Name: </label>
        <select id="sort-name" onchange="sortTable()">
            <option value="a-z">A-Z</option>
            <option value="z-a">Z-A</option>
        </select>
        <label for="sort-price">Sort by Price: </label>
        <select id="sort-price" onchange="sortTablePrice()">
            <option value="low-high">Low to High</option>
            <option value="high-low">High to Low</option>
        </select>
        <label for="sort-category">Sort by Category: </label>
    <select id="sort-category" onchange="sortTableCategory()">
        <option value="">All</option>
        <?php
        // Loop through the result to generate category options
        while ($row = mysqli_fetch_assoc($category_result)) {
           echo '<option value="' . $row['category_code'] . '">' . $row['category_name'] . '</option>';
        
        }
        ?>
    </select>
    </div>
            <div class="table-responsive">
    
            <table class="table table-hover" id="productTable">
        <thead class="table-dark">
                <th>Product Code</th>
                <th>Category Name</th>
                <th>Image</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Discount</th>   
                <th>Action</th>
          
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?php echo htmlspecialchars($product['prod_code']); ?></td>
                <td contenteditable="false"><?php echo htmlspecialchars($product['category_name']); ?></td>
                <td>
            <img src="../<?php echo htmlspecialchars($product['prod_img']); ?>" alt="Product Image" style="width: 120px; height: 50px;">
        </td>
                <td contenteditable="false"><?php echo htmlspecialchars($product['prod_name']); ?></td>
                <td contenteditable="false"><?php echo number_format($product['prod_price']); ?></td>
                 <td contenteditable="false"><?php echo number_format($product['prod_qoh']); ?></td>
                 <td contenteditable="falase"><?php echo number_format($product['prod_discount']); ?></td>
                  
                  <td>
             
                 <a href="#" class="edit-icon" data-bs-toggle="modal" data-bs-target="#editModal"
                    data-prod-code="<?php echo htmlspecialchars($product['prod_code']); ?>"
                    data-category-name="<?php echo htmlspecialchars($product['category_name']); ?>"
                    data-prod-name="<?php echo htmlspecialchars($product['prod_name']); ?>"
                    data-prod-price="<?php echo number_format($product['prod_price']); ?>"
                    data-prod-qoh="<?php echo number_format($product['prod_qoh']); ?>"
                    data-prod-discount="<?php echo number_format($product['prod_discount']); ?>" >
                    
                    <i class="fa fa-edit"></i>
            </a>
                <a href="delete_products.php?id=<?php echo $product['prod_code']; ?>" class="delete-icon" onclick="confirmDelete(event, '<?php echo $product['prod_code']; ?>')">
                    <i class="fa fa-trash"></i>
                </a>
            </td>
                
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
            </div>

            <!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editProductForm" method="post" action="update_products.php" enctype="multipart/form-data">
          <input type="hidden" name="prod_code" id="modalProdCode">
          <div class="mb-3">
            <label for="modalCategoryName" class="form-label">Category Name</label>
            <input  type="text" class="form-control" id="modalCategoryName" name="category_name" readonly>
          </div>
          <div class="mb-3">
            <label for="modalProdName" class="form-label">Product Name</label>
            <input type="text" class="form-control" id="modalProdName" name="prod_name">
          </div>
          <div class="mb-3">
            <label for="modalProdPrice" class="form-label">Price</label>
            <input type="number" class="form-control" id="modalProdPrice" name="prod_price">
          </div>
          <div class="mb-3">
            <label for="modalProdQOH" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="modalProdQOH" name="prod_qoh">
          </div>
          <div class="mb-3">
            <label for="modalProdDiscount" class="form-label">Discount</label>
            <input type="number" class="form-control" id="modalProdDiscount" name="prod_discount">
          </div>
          <div class="mb-3">
            
        <label for="modalProdImage" class="form-label">Product Image</label>
         <img id="modalImagePreview" src="" alt="Product Image" style="width: 150px; margin-bottom: 15px; height: auto; display: none;" />
        <input type="file" class="form-control" id="modalProdImage" name="prod_img" accept="image/*">
    </div>
          <button type="submit" class="btn btn-primary" >Save Changes</button>
         
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
                title: 'Product Updated',
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
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
               text: '" . $_SESSION['delete_success'] . "',
                confirmButtonText: 'OK'
            });
        });
    </script>";
     unset($_SESSION['delete_success']);
}
?>
            </div>
     
    </div>
    </div>
    <!-- /#page-content-wrapper -->
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
//edit with image show 
 var editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        
        // Extract info from data attributes
        var prodImage = button.getAttribute('data-prod-image');
        
        // Set the initial image source
        var modalImagePreview = editModal.querySelector('#modalImagePreview');
        modalImagePreview.src = prodImage;
        modalImagePreview.style.display = 'block'; // Show the image
        
        // Reset the file input
        var fileInput = editModal.querySelector('#modalProdImage');
        fileInput.value = ""; // Clear previous input

        // Show the existing image if any
        if (prodImage) {
            modalImagePreview.src = prodImage;
            modalImagePreview.style.display = 'block';
        } else {
            modalImagePreview.style.display = 'none'; // Hide if no image
        }

        // Handle the change event for file input
        fileInput.onchange = function(event) {
            var file = event.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    modalImagePreview.src = e.target.result; // Update preview with selected image
                    modalImagePreview.style.display = 'block'; // Show the updated image
                }
                reader.readAsDataURL(file); // Read the selected file
            } else {
                modalImagePreview.style.display = 'none'; // Hide if no file selected
            }
        };
    });
//copy in clipboard
document.getElementById('copyToClipboard').addEventListener('click', function() {
        const table = document.getElementById('productTable');
        let clipboardData = [];

        // Loop through the rows of the table
        for (let row of table.rows) {
            let cols = [];
            for (let cell of row.cells) {
                cols.push(cell.innerText); // Get cell text
            }
            clipboardData.push(cols.join('\t')); // Join columns with a tab
        }

        // Create a clipboard string
        const clipboardString = clipboardData.join('\n'); // Join rows with a new line

        // Copy to clipboard
        navigator.clipboard.writeText(clipboardString).then(function() {
            // Show the modal
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();

            // Automatically hide the modal after 3 seconds
            setTimeout(function() {
                successModal.hide();
            }, 1000);
        }, function(err) {
            console.error('Could not copy text: ', err);
        });
    });
//download as excel
 document.getElementById('downloadExcel').addEventListener('click', function() {
        const table = document.getElementById('productTable');
        const workbook = XLSX.utils.table_to_book(table, { sheet: "Products" });
        XLSX.writeFile(workbook, 'product_table.xlsx');
    });
//Download as pdf
 document.getElementById('downloadPDF').addEventListener('click', function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        doc.autoTable({
            html: '#productTable',
            startY: 20,
            theme: 'grid',
            headStyles: { fillColor: [0, 150, 0] },  // Custom header color
            margin: { top: 10 },
        });

        doc.save('product_table.pdf');
    });
// confirmation for deleting product
function confirmDelete(event, prodCode) {
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
            window.location.href = 'delete_products.php?id=' + prodCode;
        }
    });
}
// edit the product and for update product
document.addEventListener('DOMContentLoaded', function() {
    var editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        // Button that triggered the modal
        var button = event.relatedTarget;

        // Extract data from attributes
        var prodCode = button.getAttribute('data-prod-code');
        var categoryName = button.getAttribute('data-category-name');
        var prodName = button.getAttribute('data-prod-name');
        var prodPrice = button.getAttribute('data-prod-price');
        var prodQOH = button.getAttribute('data-prod-qoh');
        var prodDiscount = button.getAttribute('data-prod-discount');

        // Populate the modal's input fields
        document.getElementById('modalProdCode').value = prodCode;
        document.getElementById('modalCategoryName').value = categoryName;
        document.getElementById('modalProdName').value = prodName;
        document.getElementById('modalProdPrice').value = prodPrice;
        document.getElementById('modalProdQOH').value = prodQOH;
        document.getElementById('modalProdDiscount').value = prodDiscount;
    });
});

// sort table by Name
function sortTable() {
    var table, rows, switching, i, x, y, shouldSwitch;
    table = document.getElementById("productTable");
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
// sort table by price
function sortTablePrice() {
    var table = document.getElementById("productTable");
    var tbody = table.tBodies[0];
    var rows = Array.from(tbody.querySelectorAll("tr"));
    var sortValue = document.getElementById("sort-price").value;

    rows.sort(function(a, b) {
        var priceA = parseFloat(a.cells[4].innerText.replace(/,/g, ''));
        var priceB = parseFloat(b.cells[4].innerText.replace(/,/g, ''));

        if (sortValue === "low-high") {
            return priceA - priceB; 
        } else {
            return priceB - priceA; 
        }
    });

    rows.forEach(function(row) {
        tbody.appendChild(row);
    });
}
    // sort table by category
 function sortTableCategory() {
    var categoryCode = document.getElementById('sort-category').value;
    
    if (categoryCode === "") {
        location.reload();
        return;
    }
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "sortingCategory.php?category_code=" + categoryCode, true);
    xhr.onload = function () {
        if (this.status === 200) {
            document.querySelector('tbody').innerHTML = this.responseText;
        }
    };
    xhr.send();
}

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
document.getElementById('prod_img').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const imgPreview = document.getElementById('img_preview');

    if (file) {
        const reader = new FileReader();

        reader.onload = function(e) {
            imgPreview.src = e.target.result;
            imgPreview.style.display = 'block'; // Show the image preview
        };

        reader.readAsDataURL(file);
    } else {
        imgPreview.style.display = 'none'; // Hide if no file is selected
    }
});

</script>
</body>
</html>