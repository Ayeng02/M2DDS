
// Function to calculate total price
function calculateTotalPrice() {
    const quantity = parseFloat(document.getElementById('quantity').value) || 0; // Get quantity value
    const productPrice = parseFloat(document.getElementById('productPrice').value) || 0; // Get product price
    const discountAmount = parseFloat(document.getElementById('addDiscount').value) || 0; // Get discount amount

    // Calculate total price considering discount
    let totalPrice = (quantity * productPrice) - discountAmount;
    totalPrice = totalPrice < 0 ? 0 : totalPrice; // Prevent negative total price

    // Update total price field
    document.getElementById('totalPrice').value = totalPrice.toFixed(2); // Show two decimal points
}

// Event listener for quantity input
document.getElementById('quantity').addEventListener('input', calculateTotalPrice);

// Event listener for discount input
document.getElementById('addDiscount').addEventListener('input', calculateTotalPrice);

// Enable discount input when checkbox is checked
document.getElementById('applyDiscount').addEventListener('change', function() {
    const discountInput = document.getElementById('addDiscount');
    discountInput.disabled = !this.checked; // Enable or disable based on checkbox state
    if (!discountInput.disabled) {
        discountInput.focus(); // Focus the input if enabled
    } else {
        discountInput.value = 0; // Reset discount amount if disabled
        calculateTotalPrice(); // Recalculate total price without discount
    }
});

    // Check if the form is submitted
    document.getElementById('searchForm').addEventListener('submit', function(event) {
      event.preventDefault(); // Prevent the form from submitting the traditional way

      const productCode = document.getElementById('productCode').value;

      // Fetch the product using AJAX
      fetch('../cashier/check_product.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'productCode=' + encodeURIComponent(productCode),
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Update product info on the page
            document.getElementById('productName').value = data.productName;
            document.getElementById('productPrice').value = data.productPrice;
            document.getElementById('itemStocks').value = data.itemStocks;
          } else {
            // Show SweetAlert2 if product not found
            Swal.fire({
              title: 'Product Not Found',
              text: 'The product code you entered does not exist.',
              icon: 'error',
              confirmButtonText: 'OK',
            });
          }
        })
        .catch(error => {
          console.error('Error:', error);
        });
    });


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

// Function to add a new row to the product list
function addProductRow(code, name, price, qty, total) {
      const tableBody = document.getElementById('productListTable').getElementsByTagName('tbody')[0];
      const newRow = tableBody.insertRow(); // Create a new row
      const rowCount = tableBody.rows.length; // Get the current row count
      
      // Insert cells and set their values
      newRow.innerHTML = `
        <td>${rowCount}</td>
        <td>${code}</td>
        <td>${name}</td>
        <td>₱${parseFloat(price).toFixed(2)}</td>
        <td>${qty}</td>
        <td>₱${parseFloat(total).toFixed(2)}</td>
        <td>
          <button class="btn btn-danger btn-sm" onclick="removeRow(this)">
            <i class="bi bi-x-circle"></i> Remove
          </button>
        </td>
      `;
      
    }

    // Function to clear all products and reset the checkout form
    function clearAll() {
        // Clear the product list
        const productListTable = document.getElementById('productListTable').getElementsByTagName('tbody')[0];
        productListTable.innerHTML = ''; // Clear all rows
    }
    

    // Function to validate form fields and add product to the list
    function validateFields() {
      const productCode = document.getElementById('productCode').value.trim();
      const productPrice = document.getElementById('productPrice').value.trim();
      const productName = document.getElementById('productName').value.trim();
      const quantity = parseFloat(document.getElementById('quantity').value.trim());
      const itemStocks = parseFloat(document.getElementById('itemStocks').value.trim());
      const totalPrice = document.getElementById('totalPrice').value.trim();
      const applyDiscount = document.getElementById('applyDiscount').checked;
      const discountAmount = applyDiscount ? parseFloat(document.getElementById('addDiscount').value.trim()) : 0;

      // Check if any fields are empty or invalid
      if (!productCode || !productPrice || !productName || isNaN(quantity) || isNaN(itemStocks) || !totalPrice ||
          quantity <= 0 || itemStocks <= 0 || parseFloat(totalPrice) <= 0) {
        Swal.fire({
          icon: 'error',
          title: 'Validation Error',
          text: 'Please ensure all fields are filled and have valid values.',
          confirmButtonText: 'OK'
        });
        return false; // Validation failed
      }

      // Check if quantity is less than or equal to item stocks
      if (quantity > itemStocks) {
        Swal.fire({
          icon: 'error',
          title: 'Stock Error',
          text: 'Quantity cannot exceed available item stocks.',
          confirmButtonText: 'OK'
        });
        return false; // Validation failed
      }

      // Add the product row to the table
      addProductRow(productCode, productName, productPrice, quantity, totalPrice);

      // Clear input fields
      document.getElementById('productCode').value = '';
      document.getElementById('productPrice').value = '';
      document.getElementById('productName').value = '';
      document.getElementById('quantity').value = '';
      document.getElementById('itemStocks').value = '';
      document.getElementById('totalPrice').value = '';

      // Clear the discount input and disable it
  const discountInput = document.getElementById('addDiscount');
  discountInput.value = ''; // Clear the discount input
  discountInput.disabled = true; // Disable the discount input
  document.getElementById('applyDiscount').checked = false; // Uncheck the discount checkbox

      return true; // Validation passed
    }

    // Add click event listener for the Confirm button
    document.getElementById('confirmButton').addEventListener('click', function() {
      if (validateFields()) {
        // Optionally, submit the form or perform other actions here
      }
    });

    // Function to remove a row from the product list
    function removeRow(button) {
      const row = button.closest('tr'); // Get the closest row
      row.parentNode.removeChild(row); // Remove the row from the table
    }
    
    function playSuccessSound() {
        const audio = new Audio('../sound/Beep_effect.wav'); // Add the path to your sound file
        audio.volume = 1; // Set volume to maximum
        audio.play().catch(error => {
            console.error('Error playing sound:', error);
        });
    }
    

// Function to focus the table and select the first row with Alt + F
document.addEventListener('keydown', function (e) {
    if (e.altKey && e.key === 't') {
        e.preventDefault();  // Prevent default browser action
        
        const firstRow = document.querySelector('#productListTable tbody tr');
        if (firstRow) {
            // Remove focus from any previously focused row
            document.querySelectorAll('#productListTable tbody tr').forEach(row => row.classList.remove('focused'));
            
            // Add focus to the first row
            firstRow.classList.add('focused');
            firstRow.scrollIntoView();  // Bring row into view if necessary
        }
    }
});

// Function to handle row navigation with arrow keys
document.addEventListener('keydown', function (e) {
    const focusedRow = document.querySelector('#productListTable tbody tr.focused');
    if (focusedRow) {
        switch (e.key) {
            case 'ArrowDown':
                const nextRow = focusedRow.nextElementSibling;
                if (nextRow) {
                    focusedRow.classList.remove('focused');
                    nextRow.classList.add('focused');
                    nextRow.scrollIntoView({ block: "nearest" });
                }
                break;
            case 'ArrowUp':
                const prevRow = focusedRow.previousElementSibling;
                if (prevRow) {
                    focusedRow.classList.remove('focused');
                    prevRow.classList.add('focused');
                    prevRow.scrollIntoView({ block: "nearest" });
                }
                break;
        }
    }
});

// Function to remove the currently focused row with Alt + R
document.addEventListener('keydown', function (e) {
  if (e.altKey && e.key === 'r') {
      const focusedRow = document.querySelector('#productListTable tbody tr.focused');
      
      if (focusedRow) {
          Swal.fire({
              title: 'Are you sure?',
              text: 'This will remove the selected item.',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#A72828',
              cancelButtonColor: '#FF8225',
              confirmButtonText: 'Yes'
          }).then((result) => {
              if (result.isConfirmed) {
                  // Get the total price from the focused row
                  const totalPriceCell = focusedRow.cells[6]; 
                  const totalPrice = parseFloat(totalPriceCell.textContent.replace('₱', '').replace(',', '')); // Remove '₱' and convert to float
                  
                  // Subtract the total price of the removed row from the sum
                  totalPriceSum -= totalPrice;

                  // Update the total price in the checkout form
                  updateTotalPriceForm();

                  // Remove the focused row from the table
                  focusedRow.remove();

                  // Optionally focus the next available row after removal
                  const nextRow = document.querySelector('#productListTable tbody tr');
                  if (nextRow) {
                      nextRow.classList.add('focused');
                  }

                  // Display success message
                  Swal.fire({
                      title: 'Removed!',
                      text: 'The selected item has been removed.',
                      icon: 'success',
                      confirmButtonColor: '#A72828'
                  });
              }
          });
      }
  }
});


    


    let totalPriceSum = 0; // Initialize a variable to keep track of the total price sum

// Function to add a new product row to the table
function addProductRow(productCode, productName, productPrice, quantity) {
    const tableBody = document.querySelector('#productListTable tbody');
    const applyDiscount = document.getElementById('applyDiscount').checked;
    const discountAmount = applyDiscount ? parseFloat(document.getElementById('addDiscount').value.trim()) : 0;

    // Calculate the total price for the new row
    const totalPrice = (parseFloat(productPrice) * parseFloat(quantity)) - discountAmount; // Subtract discount from total

    // Ensure total price does not go below zero
    const finalTotalPrice = totalPrice < 0 ? 0 : totalPrice;

    playSuccessSound();

    // Add a new row to the table
    const row = document.createElement('tr');
    row.innerHTML = `
    <td><input type="checkbox"></td> <!-- Checkbox for selection -->
        <td>${tableBody.children.length + 1}</td>
        <td>${productCode}</td>
        <td>${productName}</td>
        <td>₱${parseFloat(productPrice).toFixed(2)}</td>
        <td>${quantity}</td>
        <td>₱${finalTotalPrice.toFixed(2)}</td>
        <td>₱${discountAmount.toFixed(2)}</td> 
        <td>
            <button class="btn btn-danger btn-sm" onclick="removeRow(this)">
                <i class="bi bi-x-circle"></i> Remove
            </button>
        </td>
    `;

    tableBody.appendChild(row);

    // Update the total price sum
    totalPriceSum += finalTotalPrice; // Add the new total price
    updateTotalPriceForm();
}

// Function to update the total price input field in the checkout form
function updateTotalPriceForm() {
    document.getElementById('totalPriceForm').value = `₱${totalPriceSum.toFixed(2)}`;
}

// Function to remove a row and update the total price
function removeRow(button) {
  Swal.fire({
      title: 'Are you sure?',
      text: 'This will remove the selected item.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#A72828',
      cancelButtonColor: '#FF8225',
      confirmButtonText: 'Yes'
  }).then((result) => {
      if (result.isConfirmed) {
          const row = button.closest('tr');
          const totalPriceCell = row.cells[5]; // Assuming the total price is in the 6th cell
          const totalPrice = parseFloat(totalPriceCell.textContent.replace('₱', '').replace(',', '')); // Remove '₱' and convert to float
          
          // Subtract the total price of the removed row from the sum
          totalPriceSum -= totalPrice;

          // Remove the row from the table
          row.remove();

          // Update the total price in the checkout form
          updateTotalPriceForm();

          // Display success message
          Swal.fire({
              title: 'Removed!',
              text: 'The selected item has been removed.',
              icon: 'success',
              confirmButtonColor: '#A72828'
          });
      }
  });
}


// Function to clear all products and reset the checkout form
function clearAll() {
  Swal.fire({
      title: 'Are you sure?',
      text: 'This will clear all items and reset the checkout form.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#A72828',
      cancelButtonColor: '#FF8225',
      confirmButtonText: 'Yes'
  }).then((result) => {
      if (result.isConfirmed) {
          // Clear all rows in the product list table
          const tableBody = document.querySelector('#productListTable tbody');
          tableBody.innerHTML = '';

          // Reset total price sum and update the checkout form
          totalPriceSum = 0;
          updateTotalPriceForm(); // Set total price to ₱0.00

          // Reset checkout form fields
          resetCheckoutForm();

          // Display success message
          Swal.fire({
              title: 'Cleared!',
              text: 'All items have been removed and the form has been reset.',
              icon: 'success',
              confirmButtonColor: '#A72828'
          });
      }
  });
}


// Function to reset the checkout form fields to their initial values
function resetCheckoutForm() {
    document.getElementById('amountReceived').value = ''; // Reset amount received to empty
    document.getElementById('change').value = '₱0.00'; // Reset change to ₱0.00
    const amountIndicator = document.getElementById('amountIndicator');
    amountIndicator.style.display = 'none'; // Hide any amount indicator
}

// Function to calculate change
function calculateChange() {
    const totalPriceElement = document.getElementById('totalPriceForm');
    const amountReceivedElement = document.getElementById('amountReceived');
    const changeElement = document.getElementById('change');
    const amountIndicator = document.getElementById('amountIndicator');

    // Get total price and amount received
    const totalPrice = parseFloat(totalPriceElement.value.replace(/[^0-9.-]+/g, "")) || 0; // Remove non-numeric characters
    const amountReceived = parseFloat(amountReceivedElement.value) || 0;

    // Reset indicator message
    amountIndicator.style.display = 'none';
    amountIndicator.textContent = '';

    // Check if the amount received is less than total price
    if (amountReceived < totalPrice && amountReceived > 0) {
        amountIndicator.style.display = 'block';
        amountIndicator.textContent = "Amount received must be greater than the total price.";
        amountIndicator.classList.remove('text-success');
        amountIndicator.classList.add('text-danger'); // Change color to red for invalid input
        changeElement.value = "₱0.00"; // Reset change field
    } else if (amountReceived >= totalPrice) {
        // Calculate change
        const change = amountReceived - totalPrice;
        changeElement.value = "₱" + change.toFixed(2); // Display change with 2 decimal places
    } else {
        // If input is not valid or zero, display a prompt
        amountIndicator.style.display = 'block';
        amountIndicator.textContent = "Please enter a valid amount received.";
        amountIndicator.classList.remove('text-success');
        amountIndicator.classList.add('text-danger'); // Change color to red
        changeElement.value = "₱0.00"; // Reset change field
    }
}
