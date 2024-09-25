
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



.category-table-container {
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
    margin-left: 5%;
   } 
   .table-header{
    background-color: #8c1c1c;
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


include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = $_POST['category_name'];
    $category_desc = $_POST['category_desc'];
    $category_img = $_FILES['category_img'];

    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "m2dds";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check for duplicate category name
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM category_tbl WHERE category_name = ?");
    $check_stmt->bind_param("s", $category_name);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => 'Category name already exists.'
        ];
    } else {
        // Check if file size is less than or equal to 2 MB
        if ($category_img['size'] > 2 * 1024 * 1024) {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Image size should not exceed 2 MB.'
            ];
        } else {
            // Check file type (JPEG and PNG only)
            $allowed_types = ['image/jpeg', 'image/png'];
            $file_type = $category_img['type'];

            if (!in_array($file_type, $allowed_types)) {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Only JPEG and PNG files are allowed.'
                ];
            } else {
                // Ensure the category directory exists
                $target_dir = "../category/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                // Process the image
                $source_image = $category_img['tmp_name'];
                $target_file = $target_dir . basename($category_img['name']);

                // Save only the relative path in the database
                $relative_path_to_store = "category/" . basename($category_img['name']);
                
                list($width, $height, $type) = getimagesize($source_image);

                if ($type == IMAGETYPE_JPEG) {
                    $src_img = imagecreatefromjpeg($source_image);
                } elseif ($type == IMAGETYPE_PNG) {
                    $src_img = imagecreatefrompng($source_image);
                }

                // Create a new true color image with desired dimensions
                $new_width = 1200;
                $new_height = 300;
                $dst_img = imagecreatetruecolor($new_width, $new_height);

                // Set the transparency for PNG images
                if ($type == IMAGETYPE_PNG) {
                    imagealphablending($dst_img, false);
                    imagesavealpha($dst_img, true);
                    $transparent = imagecolorallocatealpha($dst_img, 0, 0, 0, 127);
                    imagefill($dst_img, 0, 0, $transparent);
                }

                // Resize and crop the image
                $src_x = 0;
                $src_y = 0;
                $src_w = $width;
                $src_h = $height;

                // Calculate cropping coordinates
                if ($width / $height > $new_width / $new_height) {
                    $src_w = $height * ($new_width / $new_height);
                    $src_x = ($width - $src_w) / 2;
                } else {
                    $src_h = $width * ($new_height / $new_width);
                    $src_y = ($height - $src_h) / 2;
                }

                imagecopyresampled($dst_img, $src_img, 0, 0, $src_x, $src_y, $new_width, $new_height, $src_w, $src_h);

                // Save the image
                if ($type == IMAGETYPE_JPEG) {
                    imagejpeg($dst_img, $target_file);
                } elseif ($type == IMAGETYPE_PNG) {
                    imagepng($dst_img, $target_file);
                }

                // Free up memory
                imagedestroy($src_img);
                imagedestroy($dst_img);

                // Call the stored procedure to insert the category
                if ($stmt = $conn->prepare("CALL sp_InsertCategory(?, ?, ?)")) {
                    $stmt->bind_param("sss", $category_name, $category_desc, $relative_path_to_store);

                    if ($stmt->execute()) {
                        $_SESSION['alert'] = [
                            'icon' => 'success',
                            'title' => 'New category added successfully.'
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
            }
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

        <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group mb-3">
                    <label for="category_name">Category Name</label>
                    <input type="text" class="form-control" id="category_name" name="category_name" required>
                </div>
                <div class="form-group mb-3">
                    <label for="category_desc">Category Description</label>
                    <textarea class="form-control" id="category_desc" name="category_desc" rows="3" required></textarea>
                </div>
                <div class="form-group mb-3">
                    <label for="category_img">Category Image</label>
                    <input type="file" class="form-control-file" id="category_img" name="category_img" required accept="image/*">
                     <img id="image_preview" src="" alt="Image Preview" style="display: none; margin-top: 10px; width: 100%; height: 200px;">
                </div>
                <button type="submit" class="btn btn-primary">Add Category</button>
                </form>
            </div>
            </div>
        </div>
        </div>
        
             <div id="header-table-title">Category</div>
             <div class="d-grid gap-2 col-2 mx-auto">
            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#categoryModal">
            Add Category
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

           <button id="copyTableBtn" class="btn btn-info">Copy to Clipboard</button>
           <button id="downloadPDF" class="btn btn-danger ">Download as PDF</button>
              <button id="downloadExcel" class="btn btn-success">Download as Excel</button>
            <div class="category-table-container">
               <div class="combo-box">
                <label for="sort">Sort by category Name: </label>
                <select id="sort-name" onchange="sortTable()">
                    <option value="a-z">A-Z</option>
                    <option value="z-a">Z-A</option>
                </select>
            </div>
                <?php 
                $sql = "SELECT category_code, category_name, category_desc, category_img FROM category_tbl";
            $result = $conn->query($sql);
                ?>
           <div class="table-responsive">
            <table class="table table-hover " id="categoryTable">
                <thead class="table-dark">
                    <tr>
                        <th>Category Code</th>
                        <th>Image</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Total Product</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($category = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['category_code']); ?></td>
                            <td>
                                <?php if (!empty($category['category_img'])): ?>
                                    <img src="../<?php echo htmlspecialchars($category['category_img']); ?>" alt="Category Image" style="width: 200px; height: 50px;">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($category['category_desc']); ?></td>
                            <td>
                                <?php
                                // Query to count total products for this category
                                $cat_code = $category['category_code'];
                                $count_sql = "SELECT COUNT(*) AS total_products FROM product_tbl WHERE category_code = '$cat_code'";
                                $count_result = $conn->query($count_sql);
                                $total_products = $count_result->fetch_assoc()['total_products'];
                                echo $total_products;
                                ?>
                            </td>
                            <td>
                                <!-- Edit button trigger modal -->
                            <a href="#" class="edit-icon" data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-category-code="<?php echo htmlspecialchars($category['category_code']); ?>"
                                    data-category-name="<?php echo htmlspecialchars($category['category_name']); ?>"
                                    data-description="<?php echo htmlspecialchars($category['category_desc']); ?>"
                                    data-category-img="<?php echo htmlspecialchars($category['category_img']); ?>"> <!-- Add this -->
                                    <i class="fa fa-edit"></i>
                                </a>
                                                        
                                <!-- Delete button -->
                                <a href="delete_category.php?id=<?php echo htmlspecialchars($category['category_code']); ?>" class="delete-icon" onclick="confirmDelete(event, '<?php echo htmlspecialchars($category['category_code']); ?>')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No categories found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editCategoryForm" action="edit_category.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                        <input type="hidden" id="editCategoryCode" name="category_code">
                        <div class="mb-3">
                            <label for="editCategoryName" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="editCategoryName" name="category_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCategoryDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editCategoryDescription" name="category_desc" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editCategoryImage" class="form-label">Category Image</label>
                            <input type="file" class="form-control" id="editCategoryImage" name="category_img" accept="image/*">
                            <!-- Preview the current category image -->
                            <img id="currentCategoryImage" src="" alt="Category Image" style="width: 350px; height: 100px; margin-top: 10px;">
                        </div>
                    </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        
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
                        title: 'Category Updated',
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
    // Attach event listener to all edit icons
    const editIcons = document.querySelectorAll('.edit-icon');
    
    editIcons.forEach(icon => {
        icon.addEventListener('click', function () {
            // Get data from the clicked icon
            const categoryCode = this.getAttribute('data-category-code');
            const categoryName = this.getAttribute('data-category-name');
            const categoryDesc = this.getAttribute('data-description');
            const categoryImage = this.getAttribute('data-category-img'); // Get the image URL
            
            // Populate the modal form fields
            document.getElementById('editCategoryCode').value = categoryCode;
            document.getElementById('editCategoryName').value = categoryName;
            document.getElementById('editCategoryDescription').value = categoryDesc;
            
            // Set the current image in the preview
            document.getElementById('currentCategoryImage').src = "../category/" + categoryImage;
        });
    });
});
//show image before adding 
document.getElementById('category_img').addEventListener('change', function(event) {
    const file = event.target.files[0]; // Get the selected file
    const preview = document.getElementById('image_preview'); // Get the image preview element

    if (file) {
        const reader = new FileReader(); // Create a FileReader to read the file
        reader.onload = function(e) {
            preview.src = e.target.result; // Set the image source to the file result
            preview.style.display = 'block'; // Show the image preview
        }
        reader.readAsDataURL(file); // Read the file as a data URL
    } else {
        preview.src = ''; // Clear the image source if no file is selected
        preview.style.display = 'none'; // Hide the image preview
    }
});
//copy in clipboard
document.getElementById('copyTableBtn').addEventListener('click', function() {
    var table = document.getElementById('categoryTable');
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
        const table = document.getElementById('categoryTable');
        const workbook = XLSX.utils.table_to_book(table, { sheet: "Products" });
        XLSX.writeFile(workbook, 'category_table.xlsx');
    });
//Download as pdf
document.getElementById('downloadPDF').addEventListener('click', function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.autoTable({
        html: '#categoryTable',
        startY: 20,
        theme: 'grid',
        headStyles: { fillColor: [0, 150, 0] },  // Custom header color
        margin: { top: 10 },
    });

    doc.save('category_table.pdf');
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
            window.location.href = 'delete_category.php?id=' + prodCode;
        }
    });
}



// sort table by Name
function sortTable() {
    var table, rows, switching, i, x, y, shouldSwitch;
    table = document.getElementById("categoryTable");
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