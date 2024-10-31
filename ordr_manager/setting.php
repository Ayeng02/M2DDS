<?php
// Set error reporting to ignore notices
error_reporting(E_ALL & ~E_NOTICE);
// Database connection
include '../includes/db_connect.php';
session_start();

// Fetch user permissions based on emp_id
$emp_id = $_SESSION['emp_id']; // assuming emp_id is stored in session
$accessQuery = "SELECT add_product, edit_product, add_category FROM access_control WHERE emp_id = ?";
$stmt = $conn->prepare($accessQuery);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$accessResult = $stmt->get_result()->fetch_assoc();

$canAddProduct = $accessResult['add_product'] === 'Enabled';
$canEditProduct = $accessResult['edit_product'] === 'Enabled';
$canViewCategories = $accessResult['add_category'] === 'Enabled';

// Fetch products with category name using JOIN
$productQuery = "
    SELECT 
        p.prod_code, 
        c.category_name, 
        p.prod_name, 
        p.prod_price, 
        p.prod_discount, 
        p.prod_qoh, 
        p.prod_img 
    FROM product_tbl p
    JOIN category_tbl c ON p.category_code = c.category_code
";
$products = $conn->query($productQuery);

// Fetch categories from category_tbl
$categoryQuery = "SELECT * FROM category_tbl";
$categoryResult = $conn->query($categoryQuery);


// Fetch shop status
$shop_status_query = "SELECT shopstatus FROM shopstatus_tbl LIMIT 1";
$shop_status_result = $conn->query($shop_status_query);

$shop_status = 'Close'; // Default status
if ($shop_status_result && $shop_status_result->num_rows > 0) {
    $row = $shop_status_result->fetch_assoc();
    $shop_status = $row['shopstatus'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Manager</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.6/css/dataTables.bootstrap5.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/ordr_css.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.js">

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.2/css/buttons.bootstrap5.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

    <link rel="icon" href="../img/logo.ico" type="image/x-icon">

    <style>
        .active6 {
            background: linear-gradient(180deg, #ff83259b, #a72828);
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 24px;
            width: 24px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.3);
        }

        input:checked+.slider {
            background-color: #FF8225;
        }

        input:checked+.slider:before {
            transform: translateX(30px);
        }

        /* Tooltip Styling */
        .switch-tooltip {
            display: inline-block;
            position: relative;
        }

        .switch-tooltip:hover::after {
            content: attr(data-bs-original-title);
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: #fff;
            padding: 5px 10px;
            font-size: 0.9rem;
            border-radius: 5px;
            white-space: nowrap;
            z-index: 10;
        }
    </style>

</head>

<body>
    <!-- Sidebar on the left -->
    <?php include '../includes/omSideBar.php'; ?>

    <!-- Main content -->
    <div class="content">

        <!-- Real-time clock -->
        <div id="clock-container">
            <div id="clock"></div>
            <div id="date"></div>
        </div>

        <hr>
        <!--Switch Here--->
        <div class="container mt-5">
    <div class="d-flex flex-column align-items-end">
        <h5 style="font-weight:bold;">Shop Management</h5>
        <div class="d-flex align-items-center mt-2">
            <span class="me-2" style="margin-bottom: 10px; font-weight:bold;">Closed</span>
            <div class="switch-tooltip" data-bs-original-title="Toggle Open/Closed">
                <label class="switch">
                    <input type="checkbox" id="shopStatusSwitch" <?php echo ($shop_status === 'Open') ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>
            <span class="ms-2" style="margin-bottom: 10px; font-weight:bold;">Open</span>
        </div>
    </div>
</div>

        <hr>

        <h2 class="mb-4">Administrative Access</h2>

        <!-- Check if all permissions are disabled -->
        <?php if (!$canAddProduct && !$canEditProduct): ?>
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>No administrative access granted for add product and adding QOH. </strong> &nbsp;Please contact your administrator for access permissions.
            </div>
        <?php else: ?>
            <!-- Add Product Button (only shown if add_product is enabled) -->
            <?php if ($canAddProduct): ?>
                <button class="btn add-product-btn mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal" style="background-color: #a72828; color:#ffffff;">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            <?php endif; ?>

            <!-- Product Table (only shown if edit_product is enabled) -->
            <table id="productTable" class="table table-striped table-bordered responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Product Code</th>
                        <th>Category</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Quantity</th>
                        <?php if ($canEditProduct): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['prod_code']) ?></td>
                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                            <td>
                                <img src="../<?= htmlspecialchars($product['prod_img']) ?>" alt="Product Image" width="150" height="100">
                            </td>
                            <td><?= htmlspecialchars($product['prod_name']) ?></td>
                            <td><?= htmlspecialchars($product['prod_qoh']) ?></td>
                            <?php if ($canEditProduct): ?>
                                <td>
                                    <button class="btn btn-warning btn-sm edit-product-btn" data-prod-id="<?= $product['prod_code'] ?>">
                                        <i class="fas fa-edit"></i> Add Quantity
                                    </button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2 class="mt-5 mb-4">Category List</h2>

        <!-- Check if the user has permission to view categories -->
        <?php if (!$canViewCategories): ?>
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>No permission to view and add categories.</strong> Please contact your administrator for access permissions.
            </div>
        <?php else: ?>
            <!-- Add Category Button -->
            <button class="btn add-category-btn mb-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal" style="background-color: #a72828; color:#ffffff;">
                <i class="fas fa-plus"></i> Add Category
            </button>

            <table id="categoryTable" class="table table-striped table-bordered responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Category Code</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    if ($categoryResult->num_rows > 0) {
                        // Output data of each row
                        while ($category = $categoryResult->fetch_assoc()) {
                            echo "<tr>
                            <td>" . htmlspecialchars($category['category_code']) . "</td>
                            <td>" . htmlspecialchars($category['category_name']) . "</td>
                            <td>" . htmlspecialchars($category['category_desc']) . "</td>
                            <td><img src='../" . htmlspecialchars($category['category_img']) . "' alt='Category Image' width='200' height='100'></td>
                          </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center'>No categories found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>



        <!-- Add Product Modal -->
        <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: #a72828; color: white;">
                        <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="addProductForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row g-3">
                                <!-- Category and Product Name fields -->
                                <div class="col-md-6">
                                    <label for="category_code" class="form-label">Category</label>
                                    <select id="category_code" name="category_code" class="form-select">
                                        <option value="">Select Category</option>
                                        <?php
                                        // Populate categories
                                        while ($category = $categoryResult->fetch_assoc()) {
                                            echo '<option value="' . htmlspecialchars($category['category_code']) . '">' . htmlspecialchars($category['category_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="prod_name" class="form-label">Product Name</label>
                                    <input type="text" id="prod_name" name="prod_name" class="form-control">
                                </div>

                                <!-- Description and Price fields -->
                                <div class="col-md-6">
                                    <label for="prod_desc" class="form-label">Description</label>
                                    <textarea id="prod_desc" name="prod_desc" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="prod_price" class="form-label">Price</label>
                                    <input type="number" id="prod_price" name="prod_price" class="form-control" step="0.01">
                                </div>

                                <!-- Discount and Quantity fields -->
                                <div class="col-md-6">
                                    <label for="prod_discount" class="form-label">Discount</label>
                                    <input type="number" id="prod_discount" name="prod_discount" class="form-control" step="0.01" value="0">
                                </div>
                                <div class="col-md-6">
                                    <label for="prod_qoh" class="form-label">Quantity</label>
                                    <input type="number" id="prod_qoh" name="prod_qoh" class="form-control" step="0.01">
                                </div>

                                <!-- Product Image Upload -->
                                <div class="col-md-12">
                                    <label for="prod_img" class="form-label">Product Image</label>
                                    <input type="file" id="prod_img" name="prod_img" class="form-control" accept="image/png, image/jpeg">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn" style="background-color: #ff8225; color: white;">Add Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Quantity Modal -->
        <div class="modal fade" id="addQuantityModal" tabindex="-1" aria-labelledby="addQuantityModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: #a72828; color: white;">
                        <h5 class="modal-title" id="addQuantityModalLabel">Add Quantity</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="addQuantityForm">
                        <div class="modal-body">
                            <input type="hidden" id="prod_code" name="prod_code">
                            <div class="mb-3">
                                <label for="add_quantity" class="form-label">Enter Quantity to Add</label>
                                <input type="number" id="add_quantity" name="add_quantity" class="form-control" min="1" step="0.01">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn" style="background-color: #ff8225; color: white;">Add Quantity</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Category Modal -->
        <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addCategoryForm" method="POST" action="add_category.php" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="category_name" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="category_name" name="category_name">
                            </div>
                            <div class="mb-3">
                                <label for="category_desc" class="form-label">Description</label>
                                <textarea class="form-control" id="category_desc" name="category_desc" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="category_img" class="form-label">Image</label>
                                <input type="file" class="form-control" id="category_img" name="category_img" accept="image/jpeg, image/jpg, image/png">
                            </div>
                            <button type="submit" class="btn btn-primary">Add Category</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div> <!-- End of Container -->



    <!-- DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.6/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.6/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.bootstrap5.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="../js/order_manager.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTables with search, pagination, and responsive options
            $('#categoryTable').DataTable({
                responsive: true,
                paging: true,
                pageLength: 10, // Show 10 entries per page
                searching: true, // Enable searching
                columnDefs: [{
                    targets: [0, 1],
                    searchable: true
                }, {
                    targets: '_all',
                    searchable: false
                }]
            });

            // Form validation before submission
            $('#addCategoryForm').on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                // Get form values
                const categoryName = $('#category_name').val();
                const categoryDesc = $('#category_desc').val();
                const categoryImg = $('#category_img')[0].files[0];

                // Check if fields are empty
                if (!categoryName || !categoryDesc || !categoryImg) {
                    swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please fill in all required fields.',
                    });
                    return; // Exit the function
                }

                // Check file type and size
                const validImageTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validImageTypes.includes(categoryImg.type)) {
                    swal.fire({
                        icon: 'error',
                        title: 'Invalid File Type',
                        text: 'Only JPEG, JPG, or PNG files are allowed.',
                    });
                    return; // Exit the function
                }

                if (categoryImg.size > 5 * 1024 * 1024) { // 5MB limit
                    swal.fire({
                        icon: 'error',
                        title: 'File Size Error',
                        text: 'The file size must be less than 5MB.',
                    });
                    return; // Exit the function
                }

                // Check for duplicate category name
                $.ajax({
                    url: 'check_category.php', // New endpoint to check for duplicates
                    type: 'POST',
                    data: {
                        category_name: categoryName
                    },
                    success: function(response) {
                        const res = JSON.parse(response);
                        if (res.exists) {
                            swal.fire({
                                icon: 'error',
                                title: 'Duplicate Category',
                                text: 'This category name already exists.',
                            });
                        } else {
                            // Create FormData object
                            let formData = new FormData($('#addCategoryForm')[0]);

                            // If validation is successful, send AJAX request
                            $.ajax({
                                url: 'add_category.php',
                                type: 'POST',
                                data: formData,
                                contentType: false,
                                processData: false,
                                success: function(response) {
                                    const res = JSON.parse(response);
                                    if (res.status === 'success') {
                                        swal.fire({
                                            icon: 'success',
                                            title: 'Success',
                                            text: res.message,
                                        }).then(() => {
                                            location.reload(); // Reload the page to see the new category
                                        });
                                    } else {
                                        swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: res.message,
                                        });
                                    }
                                },
                                error: function() {
                                    swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'An error occurred. Please try again later.',
                                    });
                                }
                            });
                        }
                    },
                    error: function() {
                        swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while checking for duplicates. Please try again.',
                        });
                    }
                });
            });
        });

        $(document).ready(function() {
            // Initialize DataTables with search, pagination, and responsive options
            $('#productTable').DataTable({
                responsive: true,
                paging: true,
                pageLength: 10, // Show 10 entries per page
                searching: true, // Enable searching
                columnDefs: [{
                        targets: [0, 1, 3],
                        searchable: true
                    }, // Searchable columns (prod_code and prod_name)
                    {
                        targets: '_all',
                        searchable: false
                    } // Disable search for other columns if desired
                ]
            });

            // Handle form submission
            $('#addProductForm').on('submit', function(event) {
                event.preventDefault(); // Prevent form submission

                // Get form values
                const prodName = document.getElementById('prod_name').value.trim();
                const prodImg = document.getElementById('prod_img').files[0];
                const prodDiscount = document.getElementById('prod_discount').value || 0;
                const prodPrice = document.getElementById('prod_price').value.trim();
                const prodQoh = document.getElementById('prod_qoh').value.trim();
                const categoryCode = document.getElementById('category_code').value.trim();

                // Check if all required fields are filled out
                if (!prodName || !prodImg || !prodPrice || !prodQoh || !categoryCode) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Fields',
                        text: 'Please fill out all required fields.',
                    });
                    return;
                }

                // Check image file type
                const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
                if (!allowedTypes.includes(prodImg.type)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid File Type',
                        text: 'Only PNG and JPG/JPEG file types are allowed.',
                    });
                    return;
                }

                // Check image size (max 5MB)
                if (prodImg.size > 5 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Too Large',
                        text: 'Image size must be less than 5MB.',
                    });
                    return;
                }

                // AJAX request to check for duplicate product names
                $.ajax({
                    url: 'check_duplicate_product.php', // Endpoint to check duplicates
                    type: 'POST',
                    data: {
                        prod_name: prodName
                    },
                    success: function(response) {
                        if (response === 'duplicate') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Duplicate Product',
                                text: 'Product name already exists. Please choose a different name.',
                            });
                        } else {
                            // Initialize discount to 0 if not provided
                            document.getElementById('prod_discount').value = prodDiscount;

                            // If no duplicate, proceed to insert product
                            const formData = new FormData();
                            formData.append('category_code', categoryCode);
                            formData.append('prod_name', prodName);
                            formData.append('prod_desc', document.getElementById('prod_desc').value);
                            formData.append('prod_price', prodPrice);
                            formData.append('prod_discount', document.getElementById('prod_discount').value);
                            formData.append('prod_qoh', prodQoh);
                            formData.append('prod_img', prodImg);

                            // Insert the product by calling the stored procedure
                            $.ajax({
                                url: 'insert_product.php', // Endpoint to insert product
                                type: 'POST',
                                data: formData,
                                contentType: false,
                                processData: false,
                                success: function(insertResponse) {
                                    console.log(insertResponse); // Log the response from the server
                                    // Check if the insert was successful
                                    if (insertResponse === 'success') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Product Added',
                                            text: 'The product has been successfully added.',
                                        }).then(() => {
                                            // Reload the entire page after the SweetAlert confirmation
                                            location.reload(); // This will refresh the entire page
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'There was a problem adding the product.',
                                        });
                                    }
                                }
                            });
                        }
                    }
                });
            });
        });


        $(document).ready(function() {
            // Open Add Quantity modal and set product code
            $('.edit-product-btn').on('click', function() {
                const prodId = $(this).data('prod-id');
                $('#prod_code').val(prodId); // Set the product code in the hidden input
                $('#addQuantityModal').modal('show'); // Show the modal
            });

            // Handle form submission for adding quantity
            $('#addQuantityForm').on('submit', function(event) {
                event.preventDefault(); // Prevent default form submission

                const prodCode = $('#prod_code').val();
                const quantityToAdd = $('#add_quantity').val();

                // Check if quantity is provided
                if (!quantityToAdd) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Quantity',
                        text: 'Please enter a quantity to add.',
                    });
                    return;
                }

                // AJAX request to update the quantity
                $.ajax({
                    url: 'update_quantity.php', // Endpoint to handle the quantity update
                    type: 'POST',
                    data: {
                        prod_code: prodCode,
                        add_quantity: quantityToAdd
                    },
                    success: function(response) {
                        console.log(response); // Log the response from the server

                        if (response === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Quantity Added',
                                text: 'The quantity has been successfully added.',
                            }).then(() => {
                                $('#addQuantityModal').modal('hide'); // Hide the modal
                                location.reload(); // Reload the page to see updated quantity
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'There was a problem adding the quantity.',
                            });
                        }
                    }
                });
            });
        });

        document.getElementById('shopStatusSwitch').addEventListener('change', function() {
      let status = this.checked ? 'Open' : 'Close';

      // AJAX request to update the shop status
      fetch('update_shopstatus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `status=${status}`
      })
      .then(response => response.text())
      .then(data => {
        console.log(data); // Optional: log the response from the server
      })
      .catch(error => {
        console.error('Error:', error);
      });
    });
    </script>


</body>

</html>