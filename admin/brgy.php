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
    <title>Barangay Config</title>
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/attendanceConfig.css">


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

<div class="container-fluid mt-4">
    <!-- Card for Barangay Table -->
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-danger">Barangay Configuration</h5>
            <!-- Add Barangay Button -->
            <a href="#" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#addBarangayModal">
                <i class="fas fa-plus-circle"></i> Add Barangay
            </a>
        </div>
        <div class="card-body">
            <!-- Search Bar with Icon -->
            <div class="mb-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-secondary border-right-0">
                            <i class="fas fa-search " style="color: aliceblue;"></i>
                        </span>
                    </div>
                    <input type="text" id="searchBar" class="form-control border-left-0" placeholder="Search Barangay...">
                </div>
            </div>
            <!-- Responsive Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover border" id="brgyTable">
                    <thead class="bg-success text-white">
                        <tr>
                            <th>#</th>
                            <th>Barangay Name</th>
                            <th>Barangay Fee</th>
                            <th>Route</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM brgy_tbl ORDER BY Brgy_num ASC";
                        $result = mysqli_query($conn, $query);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>{$row['Brgy_num']}</td>";
                                echo "<td>{$row['Brgy_Name']}</td>";
                                echo "<td>₱" . number_format($row['Brgy_df'], 2) . "</td>";
                                echo "<td>{$row['brgy_route']}</td>";
                                echo "<td class='text-center'>
                                <a href='#' class='text-warning mx-1 editBarangay' data-id='{$row['Brgy_num']}'>
                                    <i class='fas fa-edit'></i>
                                </a>
                                <a href='#' class='text-danger mx-1 deleteBarangay' data-id='{$row['Brgy_num']}'>
                                    <i class='fas fa-trash-alt'></i>
                                </a>
                            </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No data available.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>




            <!-- Add Barangay Modal -->
            <div class="modal fade" id="addBarangayModal" tabindex="-1" aria-labelledby="addBarangayModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="addBarangayForm">
                            <div class="modal-header">
                                <h5 class="modal-title text-danger" id="addBarangayModalLabel">Add Barangay</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Barangay Name -->
                                <div class="form-group">
                                    <label for="barangayName">Barangay Name</label>
                                    <input type="text" name="barangay_name" id="barangayName" class="form-control" placeholder="Enter Barangay Name">
                                </div>
                                <!-- Barangay Fee -->
                                <div class="form-group">
                                    <label for="barangayFee">Barangay Fee</label>
                                    <input type="number" name="barangay_fee" id="barangayFee" class="form-control" placeholder="Enter Barangay Fee" step="0.01">
                                </div>
                                <!-- Route -->
                                <div class="form-group">
                                    <label for="barangayRoute">Route</label>
                                    <input type="text" name="barangay_route" id="barangayRoute" class="form-control" placeholder="Enter Barangay Route">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-danger">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


                <!-- Edit Barangay Modal -->
<div class="modal fade" id="editBarangayModal" tabindex="-1" aria-labelledby="editBarangayModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editBarangayForm">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="editBarangayModalLabel">Edit Barangay</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Barangay Name -->
                    <div class="form-group">
                        <label for="editBarangayName">Barangay Name</label>
                        <input type="text" name="barangay_name" id="editBarangayName" class="form-control">
                    </div>
                    <!-- Barangay Fee -->
                    <div class="form-group">
                        <label for="editBarangayFee">Barangay Fee</label>
                        <input type="number" name="barangay_fee" id="editBarangayFee" class="form-control" step="0.01">
                    </div>
                    <!-- Route -->
                    <div class="form-group">
                        <label for="editBarangayRoute">Route</label>
                        <input type="text" name="barangay_route" id="editBarangayRoute" class="form-control">
                    </div>
                    <!-- Hidden Field for Barangay ID -->
                    <input type="hidden" name="barangay_id" id="editBarangayId">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

        </div> <!--End of page content wrapper--->

    </div><!--End of wrapper--->







    <!-- Bootstrap and JavaScript -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    



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

        document.getElementById('searchBar').addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#brgyTable tbody tr');

            rows.forEach(row => {
                const barangayName = row.cells[1].textContent.toLowerCase();
                if (barangayName.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });


          // Handle form submission via AJAX
    $('#addBarangayForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'add_barangay.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const result = JSON.parse(response);
                if (result.status === 'success') {
                    $('#addBarangayModal').modal('hide');
                    $('#addBarangayForm')[0].reset(); // Reset the form
                    Swal.fire({
                        icon: 'success',
                        title: 'Barangay Added',
                        text: result.message,
                        confirmButtonColor: '#A72828'
                    }).then(() => {
                        
                        location.reload(); // Reload the table (or update dynamically)
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message,
                        confirmButtonColor: '#A72828'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to add barangay. Please try again later.',
                    confirmButtonColor: '#A72828'
                });
            }
        });
    });


     // Handle delete confirmation
     document.addEventListener('DOMContentLoaded', function () {
        const deleteButtons = document.querySelectorAll('.deleteBarangay');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const barangayId = this.getAttribute('data-id');

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
                        fetch(`delete_barangay.php?id=${barangayId}`, {
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
                                'Failed to delete barangay. Please try again.',
                                'error'
                            );
                        });
                    }
                });
            });
        });
    });


   document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.editBarangay');

    editButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            const barangayId = this.getAttribute('data-id');

            // Fetch Barangay data using AJAX
            fetch(`get_barangay.php?id=${barangayId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Populate the modal fields with the fetched data
                        document.getElementById('editBarangayName').value = data.barangay.Brgy_Name;
                        document.getElementById('editBarangayFee').value = data.barangay.Brgy_df;
                        document.getElementById('editBarangayRoute').value = data.barangay.brgy_route;
                        document.getElementById('editBarangayId').value = data.barangay.Brgy_num;

                        // Show the Edit Barangay Modal
                        $('#editBarangayModal').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                            confirmButtonColor: '#A72828'
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch Barangay details. Please try again later.',
                        confirmButtonColor: '#A72828'
                    });
                });
        });
    });

    // Ensure the modal closes properly
    const closeModal = document.querySelector('#editBarangayModal .close');
    if (closeModal) {
        closeModal.addEventListener('click', function () {
            $('#editBarangayModal').modal('hide');
        });
    }
});

// Handle form submission via AJAX for Edit Barangay
$('#editBarangayForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: 'edit_barangay.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            const result = JSON.parse(response);
            if (result.status === 'success') {
                $('#editBarangayModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Barangay Updated',
                    text: result.message,
                    confirmButtonColor: '#A72828'
                }).then(() => {
                  
                    location.reload(); // Reload the table (or update dynamically)
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message,
                    confirmButtonColor: '#A72828'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to update Barangay. Please try again later.',
                confirmButtonColor: '#A72828'
            });
        }
    });
});


    </script>

</body>

</html>