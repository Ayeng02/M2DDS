function updateClock() {
    const now = new Date();

    // Format hours, minutes, and seconds
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    // Determine AM/PM
    const ampm = hours >= 12 ? 'PM' : 'AM';

    // Convert hours from 24-hour to 12-hour format
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'

    // Format time
    const timeString = `${String(hours).padStart(2, '0')}:${minutes}:${seconds} ${ampm}`;

    // Update the clock and date elements
    document.getElementById('clock').textContent = timeString;

    // Format the date
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const day = days[now.getDay()];
    const date = now.toLocaleDateString('en-US', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
    document.getElementById('date').textContent = `${day}, ${date}`;
}

setInterval(updateClock, 1000); // Update the clock every second
updateClock(); // Initial call to display the time immediately

//Pending Table
$(document).ready(function() {
    // Initialize DataTables with pagination, and set page length to 10
    $('#ordersTable').DataTable({
        "pageLength": 10,
        "ordering": true,
        "autoWidth": false,
        "responsive": true
    });

    // Check All Checkbox functionality
    $('#checkAll').on('change', function() {
        var checkboxes = $('.orderCheckbox');
        checkboxes.prop('checked', this.checked);
    });

    // Accept Button functionality
    $('#acceptBtn').on('click', function() {
        const selectedOrders = [];
        const checkboxes = $('.orderCheckbox:checked');
        const totalOrders = $('.orderCheckbox');
        const processingNumber = $('#processingNumber').val();

        // If the user inputs a number
        if (processingNumber && processingNumber > 0) {
            // Automatically select the first `n` checkboxes based on input
            for (let i = 0; i < processingNumber && i < totalOrders.length; i++) {
                if (!totalOrders[i].checked) {
                    totalOrders[i].checked = true; // Mark the first `n` orders
                    selectedOrders.push(totalOrders[i].value);
                }
            }
        } else {
            // If no number is input, use manually checked boxes
            checkboxes.each(function() {
                selectedOrders.push($(this).val());
            });
        }

        // Validate if `selectedOrders` is not empty
        if (selectedOrders.length > 0) {
            // Send selected order IDs to the server using AJAX
            fetch('update_PenStatus.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        order_ids: selectedOrders,
                        processing_number: processingNumber // Include processing_number if needed
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success',
                            text: 'Order(s) pdated to Processing!',
                            icon: 'success'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', 'Failed to update orders: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'An error occurred while updating orders.', 'error');
                });
        } else {
            Swal.fire('Warning', 'Please select or specify the number of orders to accept.', 'warning');
        }
    });
});

//Processing Order
$(document).ready(function() {
    // Initialize DataTables with pagination, and set page length to 10
    $('#processingTable').DataTable({
        "pageLength": 10,
        "ordering": true,
        "autoWidth": false,
        "responsive": true
    });

    // Check All Checkbox functionality
    $('#processingCheck').on('change', function() {
        var checkboxes = $('.processingCheckbox');
        checkboxes.prop('checked', this.checked);
    });

    // Ship Button functionality
    $('#shipBtn').on('click', function() {
        const selectedOrders = [];
        const checkboxes = $('.processingCheckbox:checked');
        const totalOrders = $('.processingCheckbox');
        const processingNumber = $('#processOrderNumber').val();

        // If the user inputs a number
        if (processingNumber && processingNumber > 0) {
            for (let i = 0; i < processingNumber && i < totalOrders.length; i++) {
                if (!totalOrders[i].checked) {
                    totalOrders[i].checked = true; // Mark the first `n` orders
                    selectedOrders.push(totalOrders[i].value);
                }
            }
        } else {
            checkboxes.each(function() {
                selectedOrders.push($(this).val());
            });
        }

        // Ensure at least one order is selected
        if (selectedOrders.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Select Order',
                text: 'Please select at least one order to ship.',
            });
            return;
        }

        // Display the selected orders in the modal
        $('#orderId').val(selectedOrders.join(', '));

        // Load employees dynamically using AJAX
        $.ajax({
            url: 'get_shippers.php', // PHP file to fetch employee data
            method: 'GET',
            success: function(response) {
                const employees = JSON.parse(response);
                let options = '<option selected disabled>Select Employee</option>';

                employees.forEach(function(employee) {
                    options += `<option value="${employee.emp_id}">${employee.emp_name}</option>`;
                });

                $('#empId').html(options);
            },
            error: function() {
                console.error('Failed to fetch employees');
            }
        });

        // Show the modal
        $('#shipModal').modal('show');
    });

    // Confirm shipping process with Swal confirmation
    $('#confirmShip').on('click', function() {
        const empId = $('#empId').val();
        const orderIds = $('#orderId').val();
        const omId = "<?php echo $_SESSION['emp_id']; ?>"; // Pass the session emp_id

        // Ensure employee and order IDs are selected
        if (!empId || empId === 'Select Employee') {
            Swal.fire({
                icon: 'warning',
                title: 'Select Shipper',
                text: 'Please select an employee to ship the orders.',
            });
            return;
        }

        if (!orderIds || orderIds.trim() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Select Order',
                text: 'Please select at least one order to ship.',
            });
            return;
        }

        // Show SweetAlert confirmation
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to ship Order IDs: ${orderIds}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, ship it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Send data to PHP script to insert into the database
                $.ajax({
                    url: '../ordr_manager/process_shipping.php', // PHP file to handle the insertion
                    method: 'POST',
                    data: {
                        shipper_id: empId,
                        order_ids: orderIds,
                        om_id: omId 
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Shipped!',
                            text: 'The selected orders have been shipped.',
                        }).then(() => {
                            // Reload the page after a successful shipment
                            window.location.reload();
                        });

                        // Close the modal
                        $('#shipModal').modal('hide');
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to ship the orders.',
                        });
                    }
                });
            }
        });
    });
});





document.addEventListener('DOMContentLoaded', function() {
    const statusCards = document.querySelectorAll('.status-number');

    function animateNumbers() {
        statusCards.forEach(card => {
            // Add animation class
            card.classList.add('animate');

            // Remove the class after the animation ends
            card.addEventListener('animationend', function() {
                card.classList.remove('animate');
            }, {
                once: true
            });
        });
    }

    // Trigger animation on page load
    animateNumbers();
});

// Track the last order ID or timestamp
let lastOrderId = null;

function checkForNewOrders() {
    fetch('check_new_orders.php')
        .then(response => response.json())
        .then(data => {
            // Assuming 'latest_order_id' is the field returned by the server
            if (data.latest_order_id && data.latest_order_id !== lastOrderId) {
                lastOrderId = data.latest_order_id;
                window.location.reload(); // Refresh the page if new orders are found
            }
        })
        .catch(error => console.error('Error:', error));
}

// Check for new orders every 1 minute (60000 milliseconds)
setInterval(checkForNewOrders, 60000);


// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});

//logout

$(document).ready(function() {
    $('#logoutBtn').on('click', function(e) {
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
                    success: function() {
                        // Show success message and redirect to login page
                        Swal.fire({
                            icon: 'success',
                            title: 'Logged out',
                            text: 'You have been successfully logged out.',
                        }).then(() => {
                            window.location.href = '../login.php';
                        });
                    },
                    error: function() {
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

function applyFilter() {
    var filterValue = document.getElementById('orderFilter').value;

    // Send the filter selection to PHP
    $.ajax({
        url: 'filterOrder.php',
        type: 'POST',
        data: {
            filter: filterValue
        },
        success: function(data) {
            $('#processingTable tbody').html(data); // Update the table content with the response
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error); // Log any error to the console
        }
    });
}

//Print Official Receipt
$(document).ready(function() {
    $('#printAllBtn').on('click', function() {
        const selectedOrders = [];
        const checkboxes = $('.processingCheckbox:checked');
        const totalOrders = $('.processingCheckbox');
        const processingNumber = $('#processOrderNumber').val();

        if (processingNumber && processingNumber > 0) {
            for (let i = 0; i < processingNumber && i < totalOrders.length; i++) {
                if (!totalOrders[i].checked) {
                    totalOrders[i].checked = true;
                    selectedOrders.push(totalOrders[i].value);
                }
            }
        } else {
            checkboxes.each(function() {
                selectedOrders.push($(this).val());
            });
        }

        if (selectedOrders.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Select Order',
                text: 'Please select at least one order to print the Official Receipt.',
            });
            return;
        }

        // Show selected orders in modal
        const ordersList = $('#selectedOrdersList');
        ordersList.empty();
        selectedOrders.forEach(function(order) {
            ordersList.append('<li class="list_orders">Order ID: ' + order + '</li>');
        });

        // Show the confirmation modal
        $('#officialReceiptModal').modal('show');
    });

    // Handle confirm button click in the modal
    $('#confirmPrintBtn').on('click', function() {
        const selectedOrders = [];
        $('.processingCheckbox:checked').each(function() {
            selectedOrders.push($(this).val());
        });

        if (selectedOrders.length > 0) {
            // Group selected orders by cust_id and call the server to generate receipts
            $.ajax({
                url: 'generateReceipt.php',
                type: 'POST',
                data: {
                    orders: selectedOrders
                },
                success: function(response) {
                    // Call print receipt directly, grouping by cust_id and separating by page
                    const orderIds = selectedOrders.join(',');
                    window.open('printReceipt.php?orders=' + orderIds, '_blank');
                },
                error: function(err) {
                    console.error('Error generating receipts:', err);
                }
            });
        }
        // Close the modal after confirming
        $('#officialReceiptModal').modal('hide');
    });
});