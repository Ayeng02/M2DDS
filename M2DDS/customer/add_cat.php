<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Add Category</h2>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $category_name = $_POST['category_name'];
            $category_desc = $_POST['category_desc'];
            $category_img = $_FILES['category_img'];

            // Database connection
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "m2dds";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Check for duplicate category name
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM category_tbl WHERE category_name = ?");
            $check_stmt->bind_param("s", $category_name);
            $check_stmt->execute();
            $check_stmt->bind_result($count);
            $check_stmt->fetch();
            $check_stmt->close();

            if ($count > 0) {
                echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Category name already exists.",
                            showConfirmButton: true
                        });
                      </script>';
            } else {
                // Check if file size is less than or equal to 2 MB
                if ($category_img['size'] > 2 * 1024 * 1024) {
                    echo '<script>
                            Swal.fire({
                                icon: "error",
                                title: "Image size should not exceed 2 MB.",
                                showConfirmButton: true
                            });
                          </script>';
                } else {
                    // Check file type (JPEG and PNG only)
                    $allowed_types = ['image/jpeg', 'image/png'];
                    $file_type = $category_img['type'];

                    if (!in_array($file_type, $allowed_types)) {
                        echo '<script>
                                Swal.fire({
                                    icon: "error",
                                    title: "Only JPEG and PNG files are allowed.",
                                    showConfirmButton: true
                                });
                              </script>';
                    } else {
                        // Ensure the category directory exists
                        $target_dir = "category/";
                        if (!is_dir($target_dir)) {
                            mkdir($target_dir, 0777, true);
                        }

                        // Process the image
                        $source_image = $category_img['tmp_name'];
                        $target_file = $target_dir . basename($category_img['name']);
                        
                        list($width, $height, $type) = getimagesize($source_image);

                        if ($type == IMAGETYPE_JPEG) {
                            $src_img = imagecreatefromjpeg($source_image);
                        } elseif ($type == IMAGETYPE_PNG) {
                            $src_img = imagecreatefrompng($source_image);
                        }

                        // Create a new true color image with desired dimensions
                        $new_width = 1200;
                        $new_height = 300;
                        $dst_img = imagecreatetruecolor($new_width, $new_height);

                        // Set the transparency for PNG images
                        if ($type == IMAGETYPE_PNG) {
                            imagealphablending($dst_img, false);
                            imagesavealpha($dst_img, true);
                            $transparent = imagecolorallocatealpha($dst_img, 0, 0, 0, 127);
                            imagefill($dst_img, 0, 0, $transparent);
                        }

                        // Resize and crop the image
                        $src_x = 0;
                        $src_y = 0;
                        $src_w = $width;
                        $src_h = $height;

                        // Calculate cropping coordinates
                        if ($width / $height > $new_width / $new_height) {
                            $src_w = $height * ($new_width / $new_height);
                            $src_x = ($width - $src_w) / 2;
                        } else {
                            $src_h = $width * ($new_height / $new_width);
                            $src_y = ($height - $src_h) / 2;
                        }

                        imagecopyresampled($dst_img, $src_img, 0, 0, $src_x, $src_y, $new_width, $new_height, $src_w, $src_h);

                        // Save the image
                        if ($type == IMAGETYPE_JPEG) {
                            imagejpeg($dst_img, $target_file);
                        } elseif ($type == IMAGETYPE_PNG) {
                            imagepng($dst_img, $target_file);
                        }

                        // Free up memory
                        imagedestroy($src_img);
                        imagedestroy($dst_img);

                        // Call the stored procedure to insert the category
                        if ($stmt = $conn->prepare("CALL sp_InsertCategory(?, ?, ?)")) {
                            $stmt->bind_param("sss", $category_name, $category_desc, $target_file);

                            if ($stmt->execute()) {
                                echo '<script>
                                        Swal.fire({
                                            icon: "success",
                                            title: "New category added successfully.",
                                            showConfirmButton: true
                                        });
                                      </script>';
                            } else {
                                echo '<script>
                                        Swal.fire({
                                            icon: "error",
                                            title: "Database error: ' . htmlspecialchars($stmt->error) . '",
                                            showConfirmButton: true
                                        });
                                      </script>';
                            }
                            $stmt->close();
                        } else {
                            echo '<script>
                                    Swal.fire({
                                        icon: "error",
                                        title: "Failed to prepare the SQL statement.",
                                        showConfirmButton: true
                                    });
                                  </script>';
                        }
                    }
                }
            }

            $conn->close();
        }
        ?>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="category_name">Category Name</label>
                <input type="text" class="form-control" id="category_name" name="category_name" required>
            </div>
            <div class="form-group">
                <label for="category_desc">Category Description</label>
                <textarea class="form-control" id="category_desc" name="category_desc" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="category_img">Category Image</label>
                <input type="file" class="form-control-file" id="category_img" name="category_img" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Category</button>
        </form>
    </div>

    <!-- JS Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
