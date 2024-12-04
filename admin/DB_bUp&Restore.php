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

            <!--Others content here-->
            <div class="container-fluid mt-4">
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
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

// Backup Button Action
// Backup Button Action
document.getElementById('backupBtn').addEventListener('click', function () {
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
            fetch('/admin/manual-backup.php')
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
    document.getElementById('restoreBtn').addEventListener('click', function () {
        Swal.fire({
            title: 'Restore Database',
            text: 'This will overwrite the current database. Proceed with caution.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                // Placeholder for the restore action
                Swal.fire('Restored!', 'The database has been restored successfully.', 'success');
            }
        });
    });
</script>


</body>

</html>