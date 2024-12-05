<?php
session_start();
include '../includes/db_connect.php'; // Include your database connection

// Get transaction data from the URL parameters
$emp_id = $_SESSION['emp_id'];
$transactionDate = $_GET['transac_date'];
$productData = json_decode($_GET['productData'], true);
$amountReceived = $_GET['amount_received'];

// Fetch the employee name from the database
$employeeName = '';
if ($emp_id) {
    $query = "SELECT CONCAT(emp_fname, ' ', emp_lname) AS emp_name FROM emp_tbl WHERE emp_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $emp_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $employeeName);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

// Calculate the total price and fetch product names
$totalPrice = 0;

// Prepare to fetch product names
$productCodes = array_column($productData, 'prod_code');
$productNames = [];

// Fetch product names from the database
if (!empty($productCodes)) {
    // Escape product codes using the database connection
    $codesString = implode("','", array_map(function ($code) use ($conn) {
        return mysqli_real_escape_string($conn, $code);
    }, $productCodes));

    $query = "SELECT prod_code, prod_name FROM product_tbl WHERE prod_code IN ('$codesString')";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $productNames[$row['prod_code']] = $row['prod_name'];
    }
}

// Calculate total price
foreach ($productData as $product) {
    $totalPrice += $product['total_amount']; // Sum the total amounts of each product
}

// Calculate change
$posChange = $amountReceived - $totalPrice;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchased Receipt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            margin: 0;
            padding: 0;
        }

        .receipt {
            width: 500px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
        }

        .receipt h2 {
            text-align: center;
            font-size: 1.5em;
            margin-bottom: 10px;
            color: #FF8225;
            /* Brand color */
        }

        .business-info {
            text-align: center;
            margin-bottom: 15px;
        }

        .business-info img {
            display: block;
            margin: 0 auto;
            width: 100px;
            /* Adjust logo size */
            height: auto;
            /* Maintain aspect ratio */
        }

        .business-info p {
            margin: 5px 0;
            font-size: 0.9em;
        }

        .company-details {
            text-align: center;
            font-size: 0.9em;
            margin: 10px 0;
        }

        .company-details span {
            display: inline-block;
            margin: 0 5px;
        }

        .receipt table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .receipt table th,
        .receipt table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 0.9em;
        }

        .receipt table th {
            background-color: #f2f2f2;
        }

        .receipt table td {
            background-color: #ffffff;
        }

        .total {
            font-weight: bold;
            text-align: right;
        }

        .receipt p {
            text-align: center;
            margin: 5px 0;
            font-size: 0.9em;
        }

        .thank-you {
            font-weight: bold;
            font-size: 1em;
            margin-top: 20px;
            text-align: center;
            color: #28a745;
            /* Success color */
        }

        @media print {

            /* Hide default page header and footer (if supported by browser) */
            @page {
                margin: 0;
            }

            /* Hide elements that should not appear in the print */
            body {
                margin: 0;
            }

            /* Hide the top and bottom part of the page (default browser header/footer) */
            header,
            footer {
                display: none;
            }
        }
    </style>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.onload = function() {
            window.print();

            // Using setTimeout to delay the SweetAlert until after the print dialog is closed
            setTimeout(function() {
                Swal.fire({
                    title: "Printing....",
                    text: "",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Done",
                    cancelButtonText: "Stay",
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'cashier.php'; // Redirect if confirmed
                    }
                });
            }, 1000);
        };
    </script>

</head>

<body>
    <div class="receipt">
        <div class="business-info">
            <img src="../img/mtdd_logo.png" alt="Melo's Meatshop Logo">
            <h2>Melo's Meatshop</h2>
            <div class="company-details" style="font-size: 10px;">
                <span>Address: Apokon RD, Tagum City</span> |
                <span>Contact #: 09388952457</span> |
                <span>Email: melomeatshop@gmail.com</span>
            </div>
        </div>

        <p>Employee Name: <?= htmlspecialchars($employeeName); ?></p>
        <p>Transaction Date: <?= htmlspecialchars($transactionDate); ?></p>

        <table>
            <thead>
                <tr>
                    <th>Product Code</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Discount</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productData as $product) : ?>
                    <tr>
                        <td><?= htmlspecialchars($product['prod_code']); ?></td>
                        <td><?= htmlspecialchars($productNames[$product['prod_code']] ?? 'N/A'); ?></td>
                        <td><?= htmlspecialchars($product['pos_qty']); ?></td>
                        <td>₱<?= number_format($product['pos_discount'], 2); ?></td>
                        <td>₱<?= number_format($product['total_amount'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="total">Total Price: ₱<?= number_format($totalPrice, 2); ?></p>
        <p class="total">Amount Received: ₱<?= number_format($amountReceived, 2); ?></p>
        <p class="total">Change: ₱<?= number_format($posChange, 2); ?></p>
        <p class="thank-you">Thank you for your purchase!</p>
    </div>
</body>

</html>