<?php
// Set error reporting to ignore notices
error_reporting(E_ALL & ~E_NOTICE);

session_start();
include '../includes/db_connect.php';
include '../includes/sf_getEmpInfo.php';

// Redirect to landing page if already logged in
if (isset($_SESSION['EmpLogExist']) && $_SESSION['EmpLogExist'] === true || isset($_SESSION['AdminLogExist']) && $_SESSION['AdminLogExist'] === true) {
  if (isset($_SESSION['emp_role'])) {
      // Redirect based on employee role
      switch ($_SESSION['emp_role']) {
          case 'Order Manager':
              header("Location: ../ordr_manager/order_manager.php");
              exit;
          case 'Shipper':
              header("Location: ../shipper/shipper.php");
              exit;
          case 'Admin':
              header("Location: ../admin/admin_interface.php");
              exit;
          default:
              // Handle unknown roles or add default redirection if needed
              break;
      }
  }
} else {
  header("Location: ../login.php");
  exit;
}



// Initialize variables
$productCode = '';
$productName = '';
$productPrice = '';
$itemStocks = '';
$totalPrice = 0;
$discountAmount = 0;
$prodDiscount = 0;

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $searchCode = $_POST['productCode'];

  // Prepare SQL statement to search for the product
  $stmt = $conn->prepare("SELECT prod_name, prod_price, prod_qoh, prod_discount FROM product_tbl WHERE prod_code = ? OR prod_name = ?");
  $stmt->bind_param("ss", $searchCode, $searchCode);
  $stmt->execute();
  $stmt->store_result();

  // Check if a product is found
  if ($stmt->num_rows > 0) {
    $stmt->bind_result($productName, $productPrice, $itemStocks, $prodDiscount);
    $stmt->fetch();

   // Determine the product price based on discount
   if ($prodDiscount > 0) {
    $productPrice = $prodDiscount; // Use discount price if it's greater than 0
  }

    // Set session variables to hold product data (optional)
    $_SESSION['productName'] = $productName;
    $_SESSION['productPrice'] = $productPrice;
    $_SESSION['itemStocks'] = $itemStocks;
  }

  $stmt->close();

  // Redirect to the same page to prevent resubmission
  header("Location: " . $_SERVER['PHP_SELF']);
  exit(); // Ensure that the script stops executing after the redirect
}

// Optionally, retrieve product data from session to display (after redirection)
if (isset($_SESSION['productName'])) {
  $productName = $_SESSION['productName'];
  $productPrice = $_SESSION['productPrice'];
  $itemStocks = $_SESSION['itemStocks'];

  // Clear session variables after use (optional)
  unset($_SESSION['productName'], $_SESSION['productPrice'], $_SESSION['itemStocks']);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS System</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
  <link rel="stylesheet" href="../css/cashier.css">

  <style>
    .act1 {
      color: #A72828;
      font-weight: bold;
    }
  </style>
</head>

<body>

  <?php include '../includes/cashierHeader.php'; ?>

  <div class="title">
    <img src="../img/mtdd_logo.png" alt="Logo">
    Meat-To-Door POS
  </div>

  <div class="container input-container">
    <form method="post" action="" id="searchForm">
      <div class="row mb-2">
        <div class="col-sm-4">
          <label for="productCode" class="form-label">Product Code:</label>
          <div class="input-group">
            <input type="text" class="form-control" id="productCode" name="productCode" value="<?php echo htmlspecialchars($productCode); ?>" placeholder="Enter product code" required style="text-transform: uppercase;" oninput="clearFields()">
            <button class="btn btn-outline-secondary" type="submit"> <i class="bi bi-search"></i> Search</button>
          </div>
        </div>
          <div class="col-sm-4">
            <label for="productPrice" class="form-label">Product Price:</label>
            <input type="number" class="form-control" id="productPrice" value="<?php echo htmlspecialchars($productPrice); ?>" style="font-weight: bold; color:#A72828;" placeholder="₱" readonly  >
          </div>
      </div>
      <div class="row mb-2">
        <div class="col-sm-4">
          <label for="productName" class="form-label">Product Name:</label>
          <input type="text" class="form-control" id="productName" value="<?php echo htmlspecialchars($productName); ?>" style="font-weight: bold; color:#A72828;" placeholder="Product name" readonly>
        </div>
        <div class="col-sm-4">
          <label for="quantity" class="form-label">Quantity (KG):</label>
          <input type="number" class="form-control" id="quantity" placeholder="Enter quantity in KG">
        </div>
      </div>
      <div class="row mb-2">
        <div class="col-sm-4">
          <label for="itemStocks" class="form-label">Item Stocks (KG):</label>
          <input type="number" class="form-control" id="itemStocks" value="<?php echo htmlspecialchars($itemStocks); ?>" style="font-weight: bold; color:#A72828;" placeholder="Available stocks in KG" readonly>
        </div>
        <div class="col-sm-4">
          <label for="totalPrice" class="form-label">Total Price:</label>
          <input type="text" class="form-control" id="totalPrice" value="<?php echo htmlspecialchars($totalPrice); ?>" style="font-weight: bold; color:#A72828;" placeholder="₱" readonly>
        </div>
        <div class="col-sm-4">
          <div class="container border p-3" style="margin-top: -125px; margin-bottom:20px;">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="applyDiscount">
              <label class="form-check-label" for="applyDiscount">Apply Discount</label>
            </div>
            <div class="mb-2">
              <label for="addDiscount" class="form-label">Discount Amount:</label>
              <input type="number" class="form-control" id="addDiscount" value="<?php echo htmlspecialchars($discountAmount); ?>" placeholder="Enter discount" disabled>
            </div>


          </div>
          <!-- Confirm Button below Apply Discount -->
          <button type="submit" class="btn btn-success" id="confirmButton"> <i class="bi bi-check-circle"></i> Confirm</button>
        </div>
      </div>
    </form>
  </div>


  <!-- Container for Table and Form Card -->
  <div class="container mt-4">
    <div class="row d-flex">
      <!-- Left Side: Product List Card -->
      <div class="col-md-8 d-flex">
        <div class="card flex-fill">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Product List</h5>
            <button class="btn btn-danger btn-sm" onclick="clearAll()"> <i class="bi bi-trash"></i> Clear All</button>
          </div>
          <div class="card-body">
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
              <table class="table table-striped" id="productListTable">
                <thead>
                  <tr>
                    <th>Select</th>
                    <th>#</th>
                    <th>Prod Code</th>
                    <th>Prod Name</th>
                    <th>Prod Price</th>
                    <th>QTY</th>
                    <th>Total Price</th>
                    <th>Discount</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Product rows will be added here -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Side: Checkout Form Card -->
      <div class="col-md-4 d-flex">
        <div class="card flex-fill">
          <div class="card-header">
            <h5>Checkout</h5>
          </div>
          <div class="card-body">
            <form>
              <div class="mb-2">
                <label for="totalPriceForm" class="form-label">Total Price:</label>
                <input type="text" class="form-control" id="totalPriceForm" value="₱0.00" readonly style="font-weight: bold; color:#A72828;">
              </div>
              <div class="mb-2">
                <label for="amountReceived" class="form-label">Amount Received:</label>
                <input type="number" class="form-control" id="amountReceived" placeholder="Enter amount received" oninput="calculateChange()">
              </div>
              <div class="mb-2">
                <label for="change" class="form-label">Change:</label>
                <input type="text" class="form-control" id="change" value="₱0.00" readonly style="font-weight: bold; color:#A72828;">
              </div>
              <div id="amountIndicator" class="text-danger" style="display: none; padding:5px;"></div>
              <button type="button" class="btn btn-success w-100" id="checkout">
                <i class="bi bi-cart-check"></i> Check Out
              </button>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Footer -->
  <footer class="footer-widget text-center">
    <div class="container-fluid">
      <p id="currentTime" class="mb-1"></p>
      <p class="footer-text">Meat-To-Door 2024: Where Quality Meets Affordability</p>
    </div>
  </footer>



  <!-- Bootstrap JS and dependencies -->
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/cashier.js"></script>
  <script src="../js/shortcutNavigator.js" ></script>
  <!-- Script to enable discount input -->
  <script>


    document.getElementById('checkout').addEventListener('click', function() {
      const tableBody = document.querySelector('#productListTable tbody');
      const rows = tableBody.querySelectorAll('tr');
      const amountReceived = parseFloat(document.getElementById('amountReceived').value.trim());
      const posPersonnel = '<?= $_SESSION["emp_id"]; ?>'; // Get personnel ID from session
      const transactionDate = getCurrentDateTimeInManila();
      const totalPriceForm = parseFloat(document.getElementById('totalPriceForm').value.replace('₱', '').replace(',', ''));

      // Check if the table is empty
      if (rows.length === 0) {
        Swal.fire({
          icon: 'warning',
          title: 'Empty Cart',
          text: 'Please add products to the cart before proceeding to checkout.',
          confirmButtonText: 'OK'
        });
        return;
      } else if (isNaN(amountReceived) || amountReceived < totalPriceForm) {
        Swal.fire({
          icon: 'warning',
          title: 'Invalid Payment',
          text: 'Please enter a valid amount that covers the total price.',
          confirmButtonText: 'OK'
        });
        return;
      }

      // Initialize variables for total price and change calculation
      let productData = [];

      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const productCode = cells[2].innerText;
        const productPrice = parseFloat(cells[4].innerText.replace('₱', ''));
        const quantity = parseFloat(cells[5].innerText);
        const totalPrice = parseFloat(cells[6].innerText.replace('₱', ''));
        const discount = parseFloat(cells[7].innerText.replace('₱', ''));


        // Push the data into an array to send later
        productData.push({
          prod_code: productCode,
          pos_qty: quantity,
          pos_discount: discount,
          total_amount: totalPrice,
        });
      });

      // Calculate change
      const posChange = amountReceived - totalPrice;

      // Ensure amount received covers the total
      if (posChange < 0) {
        Swal.fire({
          icon: 'error',
          title: 'Insufficient Funds',
          text: 'The amount received is less than the total amount.',
          confirmButtonText: 'OK'
        });
        return;
      }

      // Send the productData and other information to the server
      $.ajax({
        url: 'processPOS.php',
        type: 'POST',
        data: {
          productData: JSON.stringify(productData), // Send the product array as JSON
          amount_received: amountReceived,
          pos_change: posChange,
          pos_personnel: posPersonnel,
          transac_date: transactionDate
        },
        success: function(response) {
          if (response === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Transaction Successful',
              text: 'The transaction has been completed.',
              confirmButtonText: 'OK'
            }).then(() => {
              // Redirect to the purchased receipt page
              window.location.href = `printReceipt.php?amount_received=${amountReceived}&pos_change=${posChange}&transac_date=${transactionDate}&productData=${encodeURIComponent(JSON.stringify(productData))}`;
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Transaction Failed',
              text: 'There was an error processing the transaction.',
              confirmButtonText: 'OK'
            });
          }
        },
        error: function(xhr, status, error) {
          console.error(error);
          Swal.fire({
            icon: 'error',
            title: 'Transaction Failed',
            text: 'An unexpected error occurred.',
            confirmButtonText: 'OK'
          });
        }
      });
    });

    function getCurrentDateTimeInManila() {
      const now = new Date();

      // Use toLocaleString to get the date and time in the Asia/Manila timezone
      const options = {
        timeZone: 'Asia/Manila',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false // Use 24-hour format
      };

      const manilaDateTime = now.toLocaleString('en-CA', options); // en-CA provides the correct format Y-m-d

      // Convert to format 'YYYY-MM-DD HH:mm:ss'
      return manilaDateTime.replace(',', '').replace(/\//g, '-');
    }

    function clearFields() {
      // Clear the productPrice, productName, itemStocks, totalPrice, and addDiscount fields
      document.getElementById('productPrice').value = '';
      document.getElementById('productName').value = '';
      document.getElementById('itemStocks').value = '';
      document.getElementById('totalPrice').value = '';
      document.getElementById('quantity').value = ''; // Clear quantity input as well if needed
      document.getElementById('addDiscount').value = ''; // Clear discount input if needed
    }

  </script>
</body>

</html>