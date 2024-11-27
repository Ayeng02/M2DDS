<?php
ob_start();
session_start();
include '../includes/db_connect.php';


// Redirect to landing page if already logged in
if (isset($_SESSION['EmpLogExist']) && $_SESSION['EmpLogExist'] === true || isset($_SESSION['AdminLogExist']) && $_SESSION['AdminLogExist'] === true) {


    if (isset($_SESSION['emp_role'])) {
        // Redirect based on employee role
        switch ($_SESSION['emp_role']) {
            case 'Shipper':
                header("Location: ../shipper/shipper.php");
                exit;
            case 'Order Manager':
                header("Location: ../ordr_manager/order_manager.php");
                exit;
            case 'Cashier':
                header("Location: ../cashier/cashier.php");
                exit;
                break;
            default:
        }
    }
} else {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers Feedback</title>
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>



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
            height: 100%;
            /* Full viewport height */
        }

        #sidebar-wrapper {
            min-height: 100vh;
            width: 80px;
            /* Default width for icons only */
            background-color: #a72828;
            color: #fff;
            transition: width 0.3s ease;
            overflow-y: auto;
            /* Allow vertical scrolling */
            position: relative;
            overflow-x: hidden;
            /* Prevent horizontal scrolling */
            border-right: 1px solid #ddd;
            /* Light border to separate from content */
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            /* Subtle shadow */

        }

        #sidebar-wrapper.expanded {
            width: 250px;
            /* Expanded width */
        }

        #sidebar-wrapper .sidebar-heading {
            padding: 1rem;
            display: flex;
            align-items: center;
            background-color: #FF8225;
            color: #fff;
            border-bottom: 1px solid #ddd;
            /* Border for separation */
        }

        #sidebar-wrapper .logo-img {
            width: 40px;
            /* Adjust size as needed */
            height: 40px;
            margin-right: 10px;
            /* Space between logo and text */
        }

        #sidebar-wrapper .sidebar-title {
            font-size: 1.5rem;
            display: inline;
            /* Ensure title is always visible */
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
            border-radius: 0;
            /* Remove default border radius */
            transition: background-color 0.2s ease;
            /* Smooth hover effect */
        }

        #sidebar-wrapper .list-group-item i {
            font-size: 1.5rem;
            margin-right: 15px;
        }

        #sidebar-wrapper .list-group-item span {
            display: none;
            /* Hide text in default state */
            margin-left: 10px;
            white-space: nowrap;
            /* Prevent text wrapping */
        }

        #sidebar-wrapper.expanded .list-group-item span {
            display: inline;
            /* Show text in expanded state */
        }

        #sidebar-wrapper .list-group-item:hover {
            background-color: #8c1c1c;
            /* Darker color on hover */
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
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
            /* Button shadow */
        }

        #sidebar-wrapper .toggle-btn:hover {
            background-color: #a72828;
        }

        #page-content-wrapper {
            flex: 1;
            padding: 20px;
            transition: margin-left 0.3s ease;
            background-color: #f8f9fa;
            /* Slightly different background */
        }

        #page-content-wrapper.sidebar-expanded {
            margin-left: 0px;
            /* Match the expanded sidebar width */
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
            width: 30px;
            /* Adjust size when collapsed */
            height: 30px;
        }


        #header-table-title {
            text-align: start;
            margin-bottom: 1%;
            margin-left: 1%;
            font-size: 45px;
            font-weight: 700;
            color: #8c1c1c;
        }

        .star-category {
            cursor: pointer;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .custom-container {
            max-width: 98%;
            margin: 0 auto;
        }


        .pagination-black .page-link {
            background-color: black;
            /* Default background color */
            color: white;
            /* Text color */
            border: 1px solid black;
            /* Border color */
            padding: 10px 20px;
            /* Padding for larger size */
            font-size: 18px;
            /* Font size */
        }

        .pagination-black .page-link:hover {
            background-color: #333;
            /* Darker shade on hover */
            color: white;
            /* Text color */
        }

        .pagination-black .page-item.active .page-link {

            background-color: #8c1c1c;
            /* Change this color to the desired active background color */
            border-color: #8c1c1c;
            /* Change the border color if needed */
            color: #fff;
            /* Active page text color */
        }

        .pagination-black .page-link:focus {
            box-shadow: none;
            /* Remove focus outline */
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
            if (isset($_POST['delete'])) {
                $reviewId = $_POST['review_id']; // Get the review ID from the form

                // Prepare the SQL query to delete the review
                $sql = "DELETE FROM ratings_tbl WHERE review_id = ?";

                if ($stmt = $conn->prepare($sql)) {
                    // Bind the review ID parameter to the query
                    $stmt->bind_param("i", $reviewId);

                    // Execute the query
                    if ($stmt->execute()) {
                        // Successfully deleted the review
                        $_SESSION['alert'] = [
                            'icon' => 'success',
                            'title' => 'Review deleted successfully.'
                        ];
                    } else {
                        // Error executing the delete query
                        $_SESSION['alert'] = [
                            'icon' => 'error',
                            'title' => 'Failed to delete the review. Please try again.'
                        ];
                    }

                    // Close the statement
                    $stmt->close();
                } else {
                    $_SESSION['alert'] = [
                        'icon' => 'error',
                        'title' => 'Error preparing statement. Please contact support.'
                    ];
                }

                // Redirect to prevent form resubmission and show the alert message
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
            ?>


            <div class="container-fluid mt-4 custom-container">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-danger">Customers Feedback</h5>
                    </div>
                    <div class="card-body">
                        <!-- Star Rating Categories -->
                        <div class="row mb-3">
                            <div class="col-4 col-sm-2">
                                <button class="btn btn-success w-100 star-filter" id="0-stars" onclick="filterReviews(0)"><i class="fa-solid fa-star" style="color: #FFD43B;"></i> All Ratings</button>
                            </div>
                            <div class="col-6 col-sm-2">
                                <button class="btn btn-secondary w-100 star-filter" id="5-stars" onclick="filterReviews(5)"><i class="fa-solid fa-star" style="color: #FFD43B;"></i> 5 Stars</button>
                            </div>
                            <div class="col-6 col-sm-2">
                                <button class="btn btn-secondary w-100 star-filter" id="4-stars" onclick="filterReviews(4)"><i class="fa-solid fa-star" style="color: #FFD43B;"></i> 4 Stars</button>
                            </div>
                            <div class="col-6 col-sm-2">
                                <button class="btn btn-secondary w-100 star-filter" id="3-stars" onclick="filterReviews(3)"><i class="fa-solid fa-star" style="color: #FFD43B;"></i> 3 Stars</button>
                            </div>
                            <div class="col-6 col-sm-2">
                                <button class="btn btn-secondary w-100 star-filter" id="2-stars" onclick="filterReviews(2)"><i class="fa-solid fa-star" style="color: #FFD43B;"></i> 2 Stars</button>
                            </div>
                            <div class="col-6 col-sm-2">
                                <button class="btn btn-secondary w-100 star-filter" id="1-star" onclick="filterReviews(1)"><i class="fa-solid fa-star" style="color: #FFD43B;"></i> 1 Star</button>
                            </div>
                        </div>

                        <!-- Reviews Container -->
                        <div id="reviews-container">
                            <?php
                            // Set the number of reviews per page
                            $reviews_per_page = 5;

                            // Get the current page number, default to 1 if not set
                            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                            $offset = ($page - 1) * $reviews_per_page;

                            // Get the selected star rating, default to 0 for all ratings
                            $star_filter = isset($_GET['star_rating']) ? (int)$_GET['star_rating'] : 0;

                            // Modify the SQL query to filter by star rating and add pagination
                            $sql_reviews = "
                            SELECT r.review_id, r.rev_message, r.rev_star, c.f_name, c.l_name, r.rev_date, pt.prod_name
                            FROM ratings_tbl r
                            JOIN Customers c ON r.cust_id = c.cust_id
                            JOIN product_tbl pt ON r.prod_code = pt.prod_code
                            WHERE r.rev_star LIKE '$star_filter' OR '$star_filter' = 0
                            ORDER BY r.rev_date DESC
                            LIMIT $reviews_per_page OFFSET $offset";

                            $result_reviews = $conn->query($sql_reviews);

                            // Count the total number of reviews for the selected star rating
                            $sql_count = "
                            SELECT COUNT(*) AS total_reviews
                            FROM ratings_tbl r
                            WHERE r.rev_star LIKE '$star_filter' OR '$star_filter' = 0";

                            $total_result = $conn->query($sql_count);
                            $total_reviews = $total_result->fetch_assoc()['total_reviews'];

                            // Calculate the total number of pages
                            $total_pages = ceil($total_reviews / $reviews_per_page);

                            $result_reviews = $conn->query($sql_reviews);

                            if ($result_reviews->num_rows > 0) {
                                while ($review = $result_reviews->fetch_assoc()) {
                                    $rev_message = htmlspecialchars($review['rev_message']);
                                    $prod_Name = htmlspecialchars($review['prod_name']);
                                    $rev_star = intval($review['rev_star']);
                                    $f_name = htmlspecialchars($review['f_name']);
                                    $l_name = htmlspecialchars($review['l_name']);
                                    $rev_date = htmlspecialchars($review['rev_date']);
                                    $review_id = intval($review['review_id']);
                                    echo "<div class='review-item d-flex justify-content-between align-items-start' data-rating='$rev_star'>
                                            <div>
                                                <p><i class='fas fa-user-circle user-icon-circle'></i> 
                                                   <strong>$f_name $l_name</strong> 
                                                   <span class='text-muted'>($rev_date)</span></p>
                                                <p>Product: <strong>$prod_Name</strong> </p> 
                                                <p>Rating: " . str_repeat('<i class="fa-solid fa-star" style="color: #FFD43B;"></i>', $rev_star) .
                                        str_repeat('<i class="fa-regular fa-star" style="color: #FFD43B;"></i>', 5 - $rev_star) . "</p>
                                                <p>$rev_message</p>
                                            </div>
                                            <form method='POST' class='ms-3'>
                                                <input type='hidden' name='review_id' value='$review_id'>
                                                <button type='submit' name='delete' class='btn btn-danger btn-sm deleteReview' data-id='$review_id'>
                                                    <i class='fa-solid fa-trash' style='color: #ffffff;'> </i> Delete
                                                </button>
                                            </form>
                                        </div>
                                        <hr>";
                                }
                            } else {
                                echo "<p>No reviews yet.</p>";
                            }


                            ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=1&star_rating=<?php echo $star_filter; ?>">First</a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&star_rating=<?php echo $star_filter; ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&star_rating=<?php echo $star_filter; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&star_rating=<?php echo $star_filter; ?>">Next</a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $total_pages; ?>&star_rating=<?php echo $star_filter; ?>">Last</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bootstrap and JavaScript -->
            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

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

                document.addEventListener('DOMContentLoaded', () => {
                    const starFiltersContainer = document.querySelector('.row.mb-3');
                    const reviews = document.querySelectorAll('.review-item');

                    // Get the selected rating from the URL (if any)
                    const urlParams = new URLSearchParams(window.location.search);
                    const selectedRating = parseInt(urlParams.get('star_rating'), 10) || 0;

                    // Update the button classes based on the selected rating
                    document.querySelectorAll('.star-filter').forEach(button => {
                        const rating = parseInt(button.id.split('-')[0], 10);

                        if (rating === selectedRating) {
                            // Highlight the selected button
                            if (rating === 0) {
                                // All Ratings button
                                button.classList.remove('btn-secondary');
                                button.classList.add('btn-primary');
                            } else {
                                // Other star-rating buttons
                                button.classList.remove('btn-secondary');
                                button.classList.add('btn-success');
                            }
                        } else {
                            // Reset unselected buttons
                            button.classList.remove('btn-primary', 'btn-success');
                            button.classList.add('btn-secondary');
                        }
                    });

                    // Add event listener for filtering reviews
                    if (starFiltersContainer) {
                        starFiltersContainer.addEventListener('click', (event) => {
                            const clickedButton = event.target.closest('.star-filter');
                            if (clickedButton) {
                                const starRating = parseInt(clickedButton.id.split('-')[0], 10) || 0;

                                // Show/Hide reviews based on the selected star rating
                                reviews.forEach(review => {
                                    const rating = parseInt(review.getAttribute('data-rating'), 10);
                                    review.style.display = (starRating === 0 || rating === starRating) ? 'block' : 'none';
                                });

                                // Update the URL with the selected star rating and reset to page 1
                                const currentUrl = new URL(window.location.href);
                                currentUrl.searchParams.set('star_rating', starRating);
                                currentUrl.searchParams.set('page', 1); // Reset to page 1 when filtering
                                window.location.href = currentUrl.toString();

                                // Update button classes
                                document.querySelectorAll('.star-filter').forEach(button => {
                                    button.classList.remove('btn-danger', 'btn-success');
                                    button.classList.add('btn-secondary');
                                });

                                if (starRating === 0) {
                                    // Highlight the All Ratings button as btn-danger
                                    clickedButton.classList.remove('btn-secondary');
                                    clickedButton.classList.add('btn-danger');
                                } else {
                                    // Highlight other buttons as btn-success
                                    clickedButton.classList.remove('btn-secondary');
                                    clickedButton.classList.add('btn-success');
                                }
                            }
                        });
                    }
                });

                document.addEventListener('DOMContentLoaded', function() {
                    const deleteButtons = document.querySelectorAll('.deleteReview');

                    deleteButtons.forEach(button => {
                        button.addEventListener('click', function(e) {
                            e.preventDefault();

                            const reviewId = this.getAttribute('data-id');

                            Swal.fire({
                                title: 'Are you sure?',
                                text: "This action cannot be undone!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#A72828',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Yes!'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Proceed with deletion
                                    fetch(`deleteReview.php?id=${reviewId}`, {
                                            method: 'GET'
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.status === 'success') {
                                                Swal.fire(
                                                    'Deleted!',
                                                    data.message,
                                                    'success'
                                                ).then(() => {
                                                    location.reload(); // Reload the page or update table dynamically
                                                });
                                            } else {
                                                Swal.fire(
                                                    'Error!',
                                                    data.message,
                                                    'error'
                                                );
                                            }
                                        })
                                        .catch(() => {
                                            Swal.fire(
                                                'Error!',
                                                'Failed to delete review Please try again.',
                                                'error'
                                            );
                                        });
                                }
                            });
                        });
                    });
                });
            </script>


</body>

</html>