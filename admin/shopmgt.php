<?php
include '../includes/db_connect.php';

// Fetch shop status
$shop_status_query = "SELECT shopstatus FROM shopstatus_tbl LIMIT 1";
$shop_status_result = $conn->query($shop_status_query);

$shop_status = 'Close'; // Default status
if ($shop_status_result && $shop_status_result->num_rows > 0) {
    $row = $shop_status_result->fetch_assoc();
    $shop_status = $row['shopstatus'];
}



// Fetch current shop background and video
$shop_bg_query = "SELECT olshopmgt_bg FROM olshopmgt_tbl LIMIT 1";  // Replace with actual table and column
$shop_vid_query = "SELECT olshopmgt_vid FROM olshopmgt_tbl LIMIT 1";  // Replace with actual table and column

$shop_bg_result = $conn->query($shop_bg_query);
$shop_vid_result = $conn->query($shop_vid_query);

$default_bg = '../img/meat-bg.png'; // Default background image
$default_vid = '../img/sampleVid.mp4'; // Default video file

$shop_bg = $default_bg; // Set default image path
$shop_vid = $default_vid; // Set default video path

if ($shop_bg_result && $shop_bg_result->num_rows > 0) {
    $row = $shop_bg_result->fetch_assoc();
    $shop_bg = $row['olshopmgt_bg']; // Replace with actual path from the database
}

if ($shop_vid_result && $shop_vid_result->num_rows > 0) {
    $row = $shop_vid_result->fetch_assoc();
    $shop_vid = $row['olshopmgt_vid']; // Replace with actual path from the database
}
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
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 24px;
            width: 24px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.3);
        }

        input:checked+.slider {
            background-color: #FF8225;
        }

        input:checked+.slider:before {
            transform: translateX(30px);
        }

        /* Tooltip Styling */
        .switch-tooltip {
            display: inline-block;
            position: relative;
        }

        .switch-tooltip:hover::after {
            content: attr(data-bs-original-title);
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: #fff;
            padding: 5px 10px;
            font-size: 0.9rem;
            border-radius: 5px;
            white-space: nowrap;
            z-index: 10;
        }

        .preview-container {
            position: relative;
            width: 100%;
            height: 300px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
            background-size: cover;
            background-position: center;
        }

        .preview-container1 {
            position: relative;
            width: 100%;
            height: auto;
            border: 1px solid #ddd;
            margin-bottom: 15px;
            background-size: cover;
            background-position: center;
        }

        .preview-container1 video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .file-input-container {
            margin-top: 20px;
        }

        .upload-btn {
            background-color: #FF8225;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .upload-btn:hover {
            background-color: #A72828;
        }

        .form-group {
            margin-bottom: 15px;
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

            <!--Switch Here--->
            <div class="container mt-4">
                <div class="card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <!-- Shop Management Title -->
                        <h5 style="font-weight:bold; margin: 0;"> <i class="fas fa-shop" style="color:crimson;"></i> Shop Management</h5>

                        <!-- Switch -->
                        <div class="d-flex align-items-center">
                            <span class="me-2" style="font-weight:bold; margin-bottom: 0;">Closed</span>
                            <div class="switch-tooltip" data-bs-original-title="Toggle Open/Closed">
                                <label class="switch">
                                    <input type="checkbox" id="shopStatusSwitch" <?php echo ($shop_status === 'Open') ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <span class="ms-2" style="font-weight:bold; margin-bottom: 0;">Open</span>
                        </div>
                    </div>
                </div>
            </div>


            <div class="container mt-4">
                <!-- Shop Background Upload Card -->
                <div class="card p-4">
                    <h5>Upload Shop Background</h5>
                    <div class="preview-container" style="background-image: url('../<?php echo $shop_bg; ?>');">
                        <!-- Default background will show if no file is selected or uploaded -->
                    </div>
                    <form action="upload_shop_bg.php" method="POST" enctype="multipart/form-data" id="imageForm">
                        <div class="form-group">
                            <label for="shopBgFile">Choose Background Image</label>
                            <input type="file" name="olshopmgt_bg" id="shopBgFile" class="form-control" onchange="previewImage(event)" required>
                        </div>
                        <button type="submit" class="upload-btn">Upload Background</button>
                    </form>
                </div>
            </div>

            <div class="container mt-4">
                <!-- Shop Video Upload Card -->
                <div class="card p-4">
                    <h5>Upload Shop Video</h5>
                    <div class="preview-container1">
                        <video controls>
                            <source src="../<?php echo $shop_vid; ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    <form action="upload_shop_vid.php" method="POST" enctype="multipart/form-data" id="videoForm">
                        <div class="form-group">
                            <label for="shopVidFile">Choose Video</label>
                            <input type="file" name="olshopmgt_vid" id="shopVidFile" class="form-control" onchange="previewVideo(event)" required>
                        </div>
                        <button type="submit" class="upload-btn">Upload Video</button>
                    </form>
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

        document.getElementById('shopStatusSwitch').addEventListener('change', function() {
            let status = this.checked ? 'Open' : 'Close';

            // AJAX request to update the shop status
            fetch('update_shopstatus.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `status=${status}`
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data); // Optional: log the response from the server
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });

        // Image preview before upload
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const previewContainer = document.querySelector('.preview-container');
                previewContainer.style.backgroundImage = `url(${reader.result})`;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        // Video preview before upload
        function previewVideo(event) {
            const previewContainer = document.querySelector('.preview-container1');
            previewContainer.innerHTML = `<video controls><source src="${URL.createObjectURL(event.target.files[0])}" type="video/mp4">Your browser does not support the video tag.</video>`;
        }


        // Image validation before form submission
        document.getElementById('imageForm').addEventListener('submit', function(event) {
            const file = document.getElementById('shopBgFile').files[0];
            const allowedTypes = ['image/jpeg', 'image/png'];

            // Check if the file size exceeds the limit
            if (file.size > 5 * 1024 * 1024) { // 5MB limit
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'File size exceeds 5MB!',
                });
            }
            // Check if the file type is allowed (JPG, JPEG, PNG)
            else if (!allowedTypes.includes(file.type)) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Only JPG, JPEG, or PNG files are allowed!',
                });
            }
            // If the image is valid, ask for confirmation before submitting
            else {
                event.preventDefault(); // Prevent form submission until confirmation
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to change the background image?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // If confirmed, submit the form
                        document.getElementById('imageForm').submit();
                    } else {

                        window.location.reload();
                    }
                });
            }
        });


        // Video validation before form submission
        document.getElementById('videoForm').addEventListener('submit', function(event) {
            const file = document.getElementById('shopVidFile').files[0];
            const allowedType = 'video/mp4';

            // Check if the file exceeds the size limit
            if (file.size > 50 * 1024 * 1024) { // 50MB limit
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'File size exceeds 50MB!',
                });
            }
            // Check if the file type is not MP4
            else if (file.type !== allowedType) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Only MP4 video files are allowed!',
                });
            } else {
                // Ask for confirmation before proceeding with the video upload
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to upload this video?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // If confirmed, submit the form
                        document.getElementById('videoForm').submit();
                    } else {
                        event.preventDefault();
                        
                    }
                });
            }
        });
    </script>

</body>

</html>