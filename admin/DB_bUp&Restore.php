<?php
session_start();
error_reporting(E_ALL & ~E_NOTICE) ;
include '../includes/db_connect.php';

$admin_id = $_SESSION['admin_id'];

// Query to check if the admin has 'super_admin' role
$query = "SELECT admin_role FROM admin_tbl WHERE admin_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Check if the admin role is 'super_admin'
$isSuperAdmin = ($row['admin_role'] == 'super_admin');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Interface</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <link rel="stylesheet" href="../css/admin.css">
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

                <?php if ($isSuperAdmin): ?>
                    <!-- Database Back-up and Restore Card -->
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-gradient-primary text-white d-flex align-items-center justify-content-between">
                            <h5 class="mb-0" style="color: crimson;"><i class="fas fa-database mr-2"></i> Database Back-up and Restore</h5>
                            <i class="fas fa-tools fa-lg" style="color: crimson;"></i>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Secure your data by performing regular backups and easily restore from existing backups when needed. These actions ensure your data is protected.
                            </p>
                            <div class="d-flex justify-content-around mt-4">
                                <!-- Backup Button -->
                                <button id="backupBtn" class="btn btn-outline-success btn-lg px-5 py-3 shadow-sm" data-toggle="tooltip" data-placement="top" title="Create a backup of the current database">
                                    <i class="fas fa-download"></i> Backup Database
                                </button>

                                <!-- Restore Button -->
                                <button id="restoreBtn" class="btn btn-outline-warning btn-lg px-5 py-3 shadow-sm" data-toggle="tooltip" data-placement="top" title="Restore the database from a previous backup">
                                    <i class="fas fa-upload"></i> Restore Database
                                </button>
                            </div>
                            <!-- Info Alert -->
                            <div class="alert alert-info mt-4 text-center" role="alert">
                                <i class="fas fa-info-circle"></i> Ensure to save backups in a secure location. Restores will overwrite the current database.
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Restricted UI Message -->
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-danger text-white d-flex align-items-center justify-content-between">
                            <h5 class="mb-0"><i class="fas fa-lock mr-2"></i> Restricted Access</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                You do not have the necessary permissions to perform database backup and restore actions.
                            </p>
                            <div class="text-center" style="margin-bottom:100px;">
                                <i class="fas fa-exclamation-circle fa-5x" style="color: crimson;"></i>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>



        </div>
    </div>

    <!-- Bootstrap and JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

    <script>
        // Initialize tooltips
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        // Backup Button Action
        // Backup Button Action
        document.getElementById('backupBtn').addEventListener('click', function() {
            Swal.fire({
                title: 'Database Backup',
                text: 'Are you sure you want to back up the database?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Backup!',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Backing Up...',
                        text: 'Please wait while the database backup is being prepared.',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Perform the backup request using Fetch API
                    fetch('manual_bakup.php')
                        .then(response => {
                            if (response.ok) {
                                // Trigger download
                                return response.blob();
                            } else {
                                throw new Error('Backup failed!');
                            }
                        })
                        .then(blob => {
                            Swal.close(); // Close the loading alert

                            // Create a downloadable link
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = url;
                            a.download = `backup_${new Date().toISOString().slice(0, 19).replace(/[-:T]/g, '')}.sql`;
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                            Swal.fire('Success!', 'Database backup was successful!', 'success');
                        })
                        .catch(error => {
                            Swal.close(); // Close the loading alert
                            Swal.fire('Error!', error.message || 'Backup failed! Please check server permissions.', 'error');
                        });
                }
            });
        });




        // Restore Button Action
        document.getElementById('restoreBtn').addEventListener('click', function() {
            Swal.fire({
                title: 'Restore Database',
                text: 'This will overwrite the current database. Proceed with caution.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Restore',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Trigger file selection for restoring backup
                    let fileInput = document.createElement('input');
                    fileInput.type = 'file';
                    fileInput.accept = '.sql';

                    fileInput.onchange = function() {
                        let formData = new FormData();
                        formData.append('backupFile', fileInput.files[0]);

                        Swal.fire({
                            title: 'Restoring Database',
                            text: 'Please wait while the restore is in progress...',
                            icon: 'info',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Send the backup file to the server for restoring
                        fetch('restore.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.text())
                            .then(data => {
                                // Close the loading state and display the result
                                Swal.close();

                                if (data.includes('Database restore complete')) {
                                    Swal.fire('Restored!', data, 'success');
                                } else {
                                    Swal.fire('Error!', 'Some issues occurred:\n' + data, 'error');
                                }
                            })
                            .catch(error => {
                                // Handle fetch errors
                                Swal.fire('Error!', 'Something went wrong while restoring the database.', 'error');
                            });
                    };

                    fileInput.click();
                }
            });
        });
    </script>


</body>

</html>