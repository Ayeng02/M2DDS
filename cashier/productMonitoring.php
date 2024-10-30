<?php
// Set error reporting to ignore notices
error_reporting(E_ALL & ~E_NOTICE);

session_start();
include '../includes/db_connect.php';
include '../includes/sf_getEmpInfo.php';

// Initialize search term
$searchTerm = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Redirect to the same page with the search term in the URL
  header("Location: " . $_SERVER['PHP_SELF'] . "?search=" . urlencode($searchTerm));
  exit(); // Make sure to exit after the redirect
}

// Get the search term from the query string if set
if (isset($_GET['search'])) {
  $searchTerm = mysqli_real_escape_string($conn, $_GET['search']);
}

// Query to get product information with optional search
$query = "SELECT prod_name, prod_img, prod_price, prod_qoh, prod_discount FROM product_tbl";
if ($searchTerm) {
  $query .= " WHERE prod_name LIKE '%$searchTerm%'";
}
$result = mysqli_query($conn, $query);
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS | Product Monitoring</title>
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
    .act2 {
      color: #A72828;
      font-weight: bold;
    }

    .product-card {
      transition: transform 0.3s ease;
    }

    .product-card:hover {
      transform: scale(1.05);
      box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
    }

    .card-title {
      font-weight: bold;
      font-size: 1.2rem;
      color: #A72828;
    }

    .card-price {
      font-size: 1.1rem;
      color: #FF8225;
    }

    .card-qoh {
      font-size: 1rem;
      color: #6c757d;
    }

    .product-container {
      padding: 20px;
    }

    .product-image {
      height: 200px;
      object-fit: cover;
    }

    .low-stock {
      background-color: #ffc107;
      padding: 5px;
      font-weight: bold;
      border-radius: 5px;
    }

    .section-title {
      font-size: 1.5rem;
      color: #A72828;
      margin-top: 20px;
      margin-bottom: 10px;
      font-weight: bold;
    }
  </style>
</head>

<body>

  <?php include '../includes/cashierHeader.php'; ?>

  <div class="title">
    <img src="../img/mtdd_logo.png" alt="Logo">
    Product Monitoring
  </div>

  <div class="container product-container">
    <!-- Search Form -->
    <form method="POST" class="mb-4">
      <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Search products by name" id="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
        <button class="btn btn-outline-secondary" type="submit">
          <i class="bi bi-search"></i> Search
        </button>

      </div>
    </form>

    <h2 class="section-title">Low Stock Products</h2>
    <div class="row">

      <?php
      // Flag to check if there are low-stock products
      $lowStockExists = false;

      // Display low-stock products (quantity on hand < 20kg)
      if (mysqli_num_rows($result) > 0) {
        mysqli_data_seek($result, 0); // Reset result pointer
        while ($row = mysqli_fetch_assoc($result)) {
          if ($row['prod_qoh'] < 20) {
            $lowStockExists = true; // Mark that low-stock products exist
            echo '<div class="col-md-3 mb-4">'; // Change to 4 columns
            echo '<div class="card product-card h-100">';
            echo '<img src="../' . $row['prod_img'] . '" class="card-img-top product-image" alt="Product Image">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . $row['prod_name'] . '</h5>';

            // Display the discount as the new price with strikethrough for original price
            if ($row['prod_discount'] > 0) {
              echo '<p class="card-price">Price: <span class="text-decoration-line-through">₱' . number_format($row['prod_price'], 2) . '</span> ₱' . number_format($row['prod_discount'], 2) . '</p>';
            } else {
              echo '<p class="card-price">Price: ₱' . number_format($row['prod_price'], 2) . '</p>';
            }

            echo '<p class="card-qoh">Quantity on hand: ' . number_format($row['prod_qoh'], 2) . ' kg</p>';
            echo '<span class="low-stock">Low Stock: Only ' . number_format($row['prod_qoh'], 2) . ' kg left!</span>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
          }
        }
      }

      // If no low-stock products, display a message
      if (!$lowStockExists) {
        echo '<div class="text-center my-4">';
        echo '<i class="bi bi-exclamation-circle" style="font-size: 3rem; color: #A72828;"></i>'; // Using Bootstrap Icon
        echo '<p class="mt-2" style="font-size: 1.2rem; color: #A72828; font-weight: bold;">No low-stock products available.</p>';
        echo '</div>';
      }
      ?>


    </div>

    <h2 class="section-title">All Products</h2>
    <div class="row">

      <?php
      // Reset result pointer and loop through again to display all products
      if (mysqli_num_rows($result) > 0) {
        mysqli_data_seek($result, 0); // Reset result pointer
        while ($row = mysqli_fetch_assoc($result)) {
          echo '<div class="col-md-3 mb-4">'; // Change to 4 columns
          echo '<div class="card product-card h-100">';
          echo '<img src="../' . $row['prod_img'] . '" class="card-img-top product-image" alt="Product Image">';
          echo '<div class="card-body">';
          echo '<h5 class="card-title">' . $row['prod_name'] . '</h5>';

          // Display the discount as the new price with strikethrough for original price
          if ($row['prod_discount'] > 0) {
            echo '<p class="card-price">Price: <span class="text-decoration-line-through">₱' . number_format($row['prod_price'], 2) . '</span> ₱' . number_format($row['prod_discount'], 2) . '</p>';
          } else {
            echo '<p class="card-price">Price: ₱' . number_format($row['prod_price'], 2) . '</p>';
          }

          echo '<p class="card-qoh">Quantity on hand: ' . number_format($row['prod_qoh'], 2) . ' kg</p>';
          echo '</div>';
          echo '</div>';
          echo '</div>';
        }
      } else {
        echo '<p>No products available.</p>';
      }
      ?>

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
  <script src="../js/shortcutNavigator.js" ></script>

  <script>
    // Function to update the time
    function updateTime() {
      const now = new Date();
      const options = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      };
      const formattedDate = now.toLocaleDateString(undefined, options);
      const formattedTime = now.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
      });

      document.getElementById('currentTime').textContent = formattedDate + ' | ' + formattedTime;
    }

    // Update time every second
    setInterval(updateTime, 1000);


    // Enable keyboard shortcuts for specific actions
    document.addEventListener('keydown', function(event) {
      // Alt + S for focusing on the SEARCH
      if (event.altKey && event.key === 's') {
        event.preventDefault();
        document.getElementById('search').focus();
      }

    });

    document.getElementById('search').addEventListener('input', function() {
      // Capitalize the first letter of the input
      this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
    });
  </script>

</body>

</html>