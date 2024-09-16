<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/jquery-ui-dist/jquery-ui.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/home.css">
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <style>
        .navbar {
            top: 0px;
        }

        /* Theme colors */
        :root {
            --primary-color: #a72828;
            --secondary-color: #FF8225;
            --card-bg: #ffffff;
            --card-border: #e0e0e0;
            --tab-bg: #f8f9fa;
            --tab-active-bg: #FF8225;
            --search-bg: #f5f5f5;
        }

        body {
            background-color: #f0f0f0;
        }

        .container {
            max-width: 1200px;
        }

        .tab-content {
            background-color: var(--tab-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 85px;
        }

        .nav-pills .nav-link {
            background-color: var(--primary-color);
            color: white;
            border-radius: 20px;
            margin-right: 10px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .nav-pills .nav-link.active {
            background-color: var(--tab-active-bg);
            transform: scale(1.1);
        }

        .tabs-section {
            margin-top: 20px;
        }

        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
            border-bottom: 1px solid var(--card-border);
            padding: 15px;
        }

        .btn-view-details {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .btn-view-details:hover {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .order-info {
            display: none;
            padding: 15px;
            background-color: var(--card-bg);
            border-top: 1px solid var(--card-border);
        }

        .input-group {
            background-color: var(--search-bg);
            border-radius: 20px;
            padding: 5px;
        }

        .pagination {
            justify-content: center;
            margin-top: 20px;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .pagination .page-link {
            border-radius: 50%;
        }

        .main-con {
            margin-top: 100px;

        }

        .navbar-light .navbar-nav .nav-link.act3 {
            color: #ffffff;
        }


        @media (max-width: 576px) {
            .order-info {
                display: block;
            }
        }
    </style>
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <div class="container my-5">
        <h1 class="mb-4 text-center main-con" style="color: var(--primary-color);">My Orders</h1>

        <!-- Order Status Tabs -->
        <ul class="nav nav-pills mb-3 justify-content-center" id="pills-tab" role="tablist">
            <li class="nav-item tabs-section" role="presentation">
                <button class="nav-link active" id="pills-all-tab" data-bs-toggle="pill" data-bs-target="#pills-all" type="button" role="tab" aria-controls="pills-all" aria-selected="true">All</button>
            </li>
            <li class="nav-item tabs-section" role="presentation">
                <button class="nav-link" id="pills-pending-tab" data-bs-toggle="pill" data-bs-target="#pills-pending" type="button" role="tab" aria-controls="pills-pending" aria-selected="false">Pending</button>
            </li>
            <li class="nav-item tabs-section" role="presentation">
                <button class="nav-link" id="pills-processing-tab" data-bs-toggle="pill" data-bs-target="#pills-processing" type="button" role="tab" aria-controls="pills-processing" aria-selected="false">Processing</button>
            </li>
            <li class="nav-item tabs-section" role="presentation">
                <button class="nav-link" id="pills-shipped-tab" data-bs-toggle="pill" data-bs-target="#pills-shipped" type="button" role="tab" aria-controls="pills-shipped" aria-selected="false">Shipped</button>
            </li>
            <li class="nav-item tabs-section" role="presentation">
                <button class="nav-link" id="pills-canceled-tab" data-bs-toggle="pill" data-bs-target="#pills-canceled" type="button" role="tab" aria-controls="pills-canceled" aria-selected="false">Canceled</button>
            </li>
            <li class="nav-item tabs-section" role="presentation">
                <button class="nav-link" id="pills-delivered-tab" data-bs-toggle="pill" data-bs-target="#pills-delivered" type="button" role="tab" aria-controls="pills-delivered" aria-selected="false">Delivered</button>
            </li>
            <li class="nav-item tabs-section" role="presentation">
                <button class="nav-link" id="pills-failed-tab" data-bs-toggle="pill" data-bs-target="#pills-failed" type="button" role="tab" aria-controls="pills-failed" aria-selected="false">Failed</button>
            </li>
        </ul>

        <!-- Date Picker -->
        <!-- Date Picker -->
        <div class="input-group mb-4">
            <input type="text" id="orderDate" class="form-control" placeholder="Select a date">
            <button class="btn btn-outline-secondary" type="button" onclick="loadOrders($('.nav-link.active').attr('data-bs-target').replace('#pills-', ''), 1, $('#orderSearch').val(), $('#orderDate').val())">Filter by Date</button>
        </div>

        <!-- Search Bar -->
        <div class="input-group mb-4">
            <input type="text" id="orderSearch" class="form-control" placeholder="Search orders...">
            <button class="btn btn-outline-secondary" type="button" onclick="searchOrders()">Search</button>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="pills-tabContent">
            <!-- All Orders -->
            <div class="tab-pane fade show active" id="pills-all" role="tabpanel" aria-labelledby="pills-all-tab">
                <div class="row" id="all-orders">
                    <!-- Orders will be loaded here via AJAX -->
                </div>
                <nav id="all-orders-pagination" class="pagination">
                    <!-- Pagination links will be loaded here via AJAX -->
                </nav>
            </div>

            <!-- Pending Orders -->
            <div class="tab-pane fade" id="pills-pending" role="tabpanel" aria-labelledby="pills-pending-tab">
                <div class="row" id="pending-orders">
                    <!-- Orders will be loaded here via AJAX -->
                </div>
                <nav id="pending-orders-pagination" class="pagination">
                    <!-- Pagination links will be loaded here via AJAX -->
                </nav>
            </div>

            <!-- Processing Orders -->
            <div class="tab-pane fade" id="pills-processing" role="tabpanel" aria-labelledby="pills-processing-tab">
                <div class="row" id="processing-orders">
                    <!-- Orders will be loaded here via AJAX -->
                </div>
                <nav id="processing-orders-pagination" class="pagination">
                    <!-- Pagination links will be loaded here via AJAX -->
                </nav>
            </div>

            <!-- Shipped Orders -->
            <div class="tab-pane fade" id="pills-shipped" role="tabpanel" aria-labelledby="pills-shipped-tab">
                <div class="row" id="shipped-orders">
                    <!-- Orders will be loaded here via AJAX -->
                </div>
                <nav id="shipped-orders-pagination" class="pagination">
                    <!-- Pagination links will be loaded here via AJAX -->
                </nav>
            </div>

            <!-- Canceled Orders -->
            <div class="tab-pane fade" id="pills-canceled" role="tabpanel" aria-labelledby="pills-canceled-tab">
                <div class="row" id="canceled-orders">
                    <!-- Orders will be loaded here via AJAX -->
                </div>
                <nav id="canceled-orders-pagination" class="pagination">
                    <!-- Pagination links will be loaded here via AJAX -->
                </nav>
            </div>

            <!-- Delivered Orders -->
            <div class="tab-pane fade" id="pills-delivered" role="tabpanel" aria-labelledby="pills-delivered-tab">
                <div class="row" id="delivered-orders">
                    <!-- Orders will be loaded here via AJAX -->
                </div>
                <nav id="delivered-orders-pagination" class="pagination">
                    <!-- Pagination links will be loaded here via AJAX -->
                </nav>
            </div>

            <!-- Failed Orders -->
            <div class="tab-pane fade" id="pills-failed" role="tabpanel" aria-labelledby="pills-failed-tab">
                <div class="row" id="failed-orders">
                    <!-- Orders will be loaded here via AJAX -->
                </div>
                <nav id="failed-orders-pagination" class="pagination">
                    <!-- Pagination links will be loaded here via AJAX -->
                </nav>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php' ?>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist/jquery-ui.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../js/notif.js"></script>
    <script>
        $(document).ready(function() {

             // Initialize the date picker
             $('#orderDate').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true
            });

            // Initial load of orders
            loadOrders('all');

            // Event for tab switching
            $('#pills-tab button').on('click', function() {
                let tabId = $(this).attr('data-bs-target').replace('#pills-', '');
                loadOrders(tabId);
            });

            // Event for search functionality
            $('#orderSearch').on('input', function() {
                let query = $(this).val();
                if (query.length > 2) {
                    searchOrders(query);
                } else {
                    loadOrders($('.nav-link.active').attr('data-bs-target').replace('#pills-', ''));
                }
            });
        });

         function loadOrders(status, page = 1, search = '', date = '') {
            $.ajax({
                url: 'fetch_orders.php',
                type: 'GET',
                data: {
                    status: status,
                    page: page,
                    search: search,
                    date: date
                },
                success: function(response) {
                    let data = JSON.parse(response);
                    $(`#${status}-orders`).html(data.orders);
                    $(`#${status}-orders-pagination`).html(data.pagination);
                    bindDetailsToggle(); // Bind details toggle after loading content
                }
            });
        }

        function searchOrders(query) {
            let status = $('.nav-link.active').attr('data-bs-target').replace('#pills-', '');
            loadOrders(status, 1, query);
        }

        function bindDetailsToggle() {
            $('.btn-view-details').on('click', function() {
                $(this).siblings('.order-info').slideToggle();
            });
        }

        function cancelOrder(orderId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes!',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading spinner
                    Swal.fire({
                        title: 'Cancelling...',
                        text: 'Please wait while we process your request.',
                        icon: 'info',
                        showConfirmButton: false, // No OK button
                        allowOutsideClick: false, // Prevent closing by clicking outside
                        didOpen: () => {
                            Swal.showLoading(); // Show the loading spinner
                        }
                    });

                    $.ajax({
                        url: 'cancel_order.php',
                        type: 'POST',
                        data: {
                            order_id: orderId
                        },
                        dataType: 'json',
                        success: function(response) {
                            Swal.close(); // Close the loading spinner
                            if (response.success) {
                                Swal.fire({
                                    title: 'Cancelled!',
                                    text: 'Your order has been cancelled.',
                                    icon: 'success',
                                    showConfirmButton: false, // No OK button
                                    timer: 2000 // Auto-close after 2 seconds
                                });
                                // Update the order status on the page without refreshing
                                $('#order-card-' + orderId).find('.order-status').text('Canceled');
                                $('#order-card-' + orderId).find('.cancel-btn').remove(); // Remove the cancel button
                            } else {
                                Swal.fire({
                                    title: 'Failed!',
                                    text: 'Failed to cancel the order: ' + response.error,
                                    icon: 'error',
                                    showConfirmButton: false, // No OK button
                                    timer: 3000 // Auto-close after 3 seconds
                                });
                            }
                        },
                        error: function() {
                            Swal.close(); // Close the loading spinner
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while processing your request.',
                                icon: 'error',
                                showConfirmButton: false, // No OK button
                                timer: 3000 // Auto-close after 3 seconds
                            });
                        }
                    });
                }
            });
        }
    </script>
</body>

</html>