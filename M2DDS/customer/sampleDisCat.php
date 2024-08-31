<?php
require 'vendor/autoload.php'; // Include the Composer autoload file

use Picqer\Barcode\BarcodeGeneratorPNG;

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "m2dds";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch categories from the database
$sql = "SELECT * FROM category_tbl";
$result = $conn->query($sql);

// Check if there are results
if ($result->num_rows > 0) {
    // Fetch all results
    $categories = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $categories = [];
}

$conn->close();

// Generate barcodes for each category
$barcodeDataUris = [];
$generator = new BarcodeGeneratorPNG();
foreach ($categories as $category) {
    $barcode = $generator->getBarcode($category['category_code'], $generator::TYPE_CODE_128);
    $barcodeDataUris[$category['category_code']] = 'data:image/png;base64,' . base64_encode($barcode);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-container {
            margin-top: 30px;
        }
        .btn-edit, .btn-delete, .btn-print, .btn-print-all {
            margin-right: 5px;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .btn-print {
            background-color: #17a2b8;
            color: white;
        }
        .btn-print:hover {
            background-color: #138496;
        }
        .btn-print-all {
            background-color: #28a745;
            color: white;
        }
        .btn-print-all:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Categories</h2>

        <button class="btn btn-print-all" onclick="printAllCategories()">Print All Categories</button>

        <div class="table-container">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Category Code</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Image</th>
                        <th>Barcode</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['category_code']); ?></td>
                            <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($category['category_desc']); ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($category['category_img']); ?>" alt="Category Image" style="width: 100px; height: auto;">
                            </td>
                            <td>
                                <img src="<?php echo $barcodeDataUris[$category['category_code']]; ?>" alt="Barcode" style="width: 150px; height: auto;">
                            </td>
                            <td>
                                <a href="edit_category.php?code=<?php echo urlencode($category['category_code']); ?>" class="btn btn-primary btn-edit">Edit</a>
                                <button class="btn btn-delete" data-code="<?php echo htmlspecialchars($category['category_code']); ?>">Delete</button>
                                <button class="btn btn-print"
                                    data-code="<?php echo htmlspecialchars($category['category_code']); ?>"
                                    data-name="<?php echo htmlspecialchars($category['category_name']); ?>"
                                    data-desc="<?php echo htmlspecialchars($category['category_desc']); ?>"
                                    data-img="<?php echo htmlspecialchars($category['category_img']); ?>"
                                    data-barcode="<?php echo $barcodeDataUris[$category['category_code']]; ?>"
                                    onclick="printCategoryDetails(
                                        '<?php echo htmlspecialchars($category['category_code']); ?>',
                                        '<?php echo htmlspecialchars($category['category_name']); ?>',
                                        '<?php echo htmlspecialchars($category['category_desc']); ?>',
                                        '<?php echo htmlspecialchars($category['category_img']); ?>',
                                        '<?php echo $barcodeDataUris[$category['category_code']]; ?>'
                                    )">Print</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JS Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function () {
                const categoryCode = this.getAttribute('data-code');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to recover this category!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect or send an AJAX request to delete the category
                        window.location.href = 'delete_category.php?code=' + encodeURIComponent(categoryCode);
                    }
                });
            });
        });

        function printCategoryDetails(code, name, description, image, barcodeDataUri) {
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Print Category Details</title>');
            printWindow.document.write('<style>body { font-family: Arial, sans-serif; margin: 20px; } .container { max-width: 800px; margin: auto; } img { max-width: 100%; height: auto; } h1 { font-size: 20px; } p { margin: 10px 0; }</style>');
            printWindow.document.write('</head><body >');
            printWindow.document.write('<div class="container">');
            printWindow.document.write('<h1>Category Details</h1>');
            printWindow.document.write('<p><strong>Category Code:</strong> ' + code + '</p>');
            printWindow.document.write('<p><strong>Category Name:</strong> ' + name + '</p>');
            printWindow.document.write('<p><strong>Description:</strong> ' + description + '</p>');
            printWindow.document.write('<p><strong>Image:</strong></p>');
            printWindow.document.write('<img src="' + image + '" alt="Category Image">');
            printWindow.document.write('<p><strong>Barcode:</strong></p>');
            printWindow.document.write('<img src="' + barcodeDataUri + '" alt="Barcode">');
            printWindow.document.write('</div>');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }

        function printAllCategories() {
            const categories = <?php echo json_encode($categories); ?>;
            let content = '<html><head><title>Print All Categories</title>';
            content += '<style>body { font-family: Arial, sans-serif; margin: 20px; } .container { max-width: 800px; margin: auto; } img { max-width: 100%; height: auto; } h1 { font-size: 20px; } p { margin: 10px 0; } </style>';
            content += '</head><body>';
            content += '<div class="container">';
            content += '<h1>All Categories</h1>';

            categories.forEach(category => {
                const barcodeDataUri = <?php echo json_encode($barcodeDataUris); ?>[category['category_code']];

                content += '<h2>Category Details</h2>';
                content += '<p><strong>Category Code:</strong> ' + category['category_code'] + '</p>';
                content += '<p><strong>Category Name:</strong> ' + category['category_name'] + '</p>';
                content += '<p><strong>Description:</strong> ' + category['category_desc'] + '</p>';
                content += '<p><strong>Image:</strong></p>';
                content += '<img src="' + category['category_img'] + '" alt="Category Image">';
                content += '<p><strong>Barcode:</strong></p>';
                content += '<img src="' + barcodeDataUri + '" alt="Barcode">';
                content += '<hr>';
            });
            content += '</div>';
            content += '</body></html>';

            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write(content);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }
    </script>

</body>
</html>
