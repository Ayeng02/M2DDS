// Enable keyboard shortcuts for specific actions
document.addEventListener('keydown', function (event) {
  // Alt + S for focusing on the product code input
  if (event.altKey && event.key === 's') {
    event.preventDefault();
    document.getElementById('productCode').focus();
  }

  // Alt + Q for focusing on the quantity input
  if (event.altKey && event.key === 'q') {
    event.preventDefault();
    document.getElementById('quantity').focus();
  }

  // Alt + D for toggling the discount checkbox and focusing on discount input
  if (event.altKey && event.key === 'd') {
    event.preventDefault();
    const discountCheckbox = document.getElementById('applyDiscount');
    discountCheckbox.checked = !discountCheckbox.checked; // Toggle checkbox
    const discountInput = document.getElementById('addDiscount');
    discountInput.disabled = !discountCheckbox.checked; // Enable/disable input
    if (!discountInput.disabled) {
      discountInput.focus();
    }
  }

  // Alt + K for clearing specific fields
  if (event.altKey && event.key === 'k') {
    event.preventDefault();
    clearFields(); // Call the clearFields function
  }
  // Clear fields function
  function clearFields() {
    document.getElementById('productCode').value = ''; // Clear product code
    document.getElementById('productName').value = ''; // Clear product name
    document.getElementById('productPrice').value = ''; // Clear product price
    document.getElementById('itemStocks').value = ''; // Clear quantity
  }

  // Alt + A for focusing on the amount received input
  if (event.altKey && event.key === 'a') {
    event.preventDefault();
    document.getElementById('amountReceived').focus();
  }

  // Alt + C for triggering checkout
  if (event.altKey && event.key === 'c') {
    event.preventDefault();
    document.getElementById('checkout').click();

  }

  // Ctrl + Enter for triggering confirm button
  if (event.ctrlKey && event.key === 'Enter') {
    event.preventDefault(); // Prevent default behavior
    document.getElementById('confirmButton').click(); // Simulate button click
  }

  // Alt + X for clearing all products
  if (event.altKey && event.key === 'x') {
    event.preventDefault();
    clearAll(); // Call the clearAll function
  }

  //Shiftkey + Alt + C for pos
  if (event.altKey && event.shiftKey && event.key === 'C') {
    event.preventDefault();
    window.location.href = "cashier.php";
  }

  //Shiftkey + Alt +  P for product monitoring
  if (event.altKey && event.shiftKey && event.key === 'P') {
    event.preventDefault();
    window.location.href = "productMonitoring.php";
  }

  //Shiftkey + Alt +  S for Sales Report
  if (event.altKey && event.shiftKey && event.key === 'S') {
    event.preventDefault();
    window.location.href = "cashierSales.php";
  }

  //Shiftkey + Alt +  D for DTR
  if (event.altKey && event.shiftKey && event.key === 'D') {
    event.preventDefault();
    window.location.href = "dtr.php";
  }

  //Shiftkey + Alt +  R for transaction logs
  if (event.altKey && event.shiftKey && event.key === 'R') {
    event.preventDefault();
    window.location.href = "report.php";
  }

  // Alt + l for logout
  if (event.altKey && event.key === 'l') {
    event.preventDefault(); // Prevent default behavior
    document.getElementById('logoutBtn').click(); // Simulate button click
  }



});


$(document).ready(function () {
  $('#logoutBtn').on('click', function (e) {
    e.preventDefault(); // Prevent the default link behavior

    // Show SweetAlert confirmation before logging out
    Swal.fire({
      title: 'Are you sure?',
      text: 'You will be logged out of the system.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes',
      cancelButtonText: 'Cancel',
      reverseButtons: false
    }).then((result) => {
      if (result.isConfirmed) {
        // Perform AJAX call to logout
        $.ajax({
          url: '../includes/logout.php', // Path to your logout PHP script
          method: 'POST',
          success: function () {
            // Show success message and redirect to login page
            Swal.fire({
              icon: 'success',
              title: 'Logged out',
              text: 'You have been successfully logged out.',
              showConfirmButton: false,
              timer: 1000
            }).then(() => {
              window.location.href = '../login.php'; // Redirect to login page
            });
          },
          error: function () {
            // Show error message if logout fails
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Failed to log out. Please try again.',
            });
          }
        });
      }
    });
  });
});