<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Edit Photo";
$msg = "";
$error_msg = "";
$photo_id = null;
$photo = null;

// Define upload directory
$upload_dir_server = "../public/uploads/photos/";
$upload_dir_web = "../public/uploads/photos/";

// Get photo ID from URL
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $photo_id = $_GET['id'];
} else {
    header("Location: manage_photos.php?error=Invalid Photo ID");
    exit;
}

// Fetch existing photo data
$sql_fetch = "SELECT id, title, description, image_url, gallery_tag FROM photos WHERE id = " . $photo_id;
$result_fetch = mysqli_query($conn, $sql_fetch);
if ($result_fetch && mysqli_num_rows($result_fetch) == 1) {
    $photo = mysqli_fetch_assoc($result_fetch);
} else {
    header("Location: manage_photos.php?error=Photo not found");
    exit;
}
mysqli_free_result($result_fetch);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $posted_photo_id = filter_input(INPUT_POST, 'photo_id', FILTER_VALIDATE_INT);
    if ($posted_photo_id !== $photo_id) {
        $error_msg = "Error: Photo ID mismatch.";
    } else {
        $title = sanitize_input($conn, $_POST['title']);
        $description = sanitize_input($conn, $_POST['description']);
        $gallery_tag = sanitize_input($conn, $_POST['gallery_tag']);

        $current_image_url = $photo['image_url']; // Existing image path

        // Image Upload Handling (if a new image is provided)
        if (isset($_FILES["new_image"]) && $_FILES["new_image"]["error"] == 0) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $file_name = basename($_FILES["new_image"]["name"]);
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (!is_dir($upload_dir_server)) {
                mkdir($upload_dir_server, 0777, true);
            }
            $target_file_server = $upload_dir_server . uniqid() . '_' . $file_name;
            $target_file_web = str_replace('../public/', '../public/', $target_file_server);

            if (in_array($file_type, $allowed_types)) {
                if ($_FILES["new_image"]["size"] < 10000000) { // 10MB limit
                    if (move_uploaded_file($_FILES["new_image"]["tmp_name"], $target_file_server)) {
                        // Delete old image if a new one is successfully uploaded
                        if (!empty($current_image_url)) {
                            $old_image_server_path = realpath(dirname(__FILE__) . '/' . $current_image_url);
                            if ($old_image_server_path && file_exists($old_image_server_path)) {
                                unlink($old_image_server_path);
                            }
                        }
                        $current_image_url = $target_file_web; // Update with new image path
                    } else {
                        $error_msg .= "Sorry, there was an error uploading your new file. ";
                    }
                } else {
                    $error_msg .= "Sorry, your new file is too large. Max 10MB. ";
                }
            } else {
                $error_msg .= "Sorry, only JPG, JPEG, PNG, GIF & WEBP files are allowed for new image. ";
            }
        } elseif (isset($_FILES["new_image"]) && $_FILES["new_image"]["error"] != UPLOAD_ERR_NO_FILE) {
             $error_msg .= "Error during new file upload: " . $_FILES["new_image"]["error"] . ". ";
        }


        if (empty($title)) {
            $error_msg .= "Photo title is required. ";
        }

        if (empty($error_msg)) {
            $sql_update = "UPDATE photos SET
                title = '" . $title . "',
                description = '" . $description . "',
                image_url = '" . $current_image_url . "',
                gallery_tag = '" . $gallery_tag . "'
                WHERE id = " . $photo_id;

            if (mysqli_query($conn, $sql_update)) {
                $msg = "Photo details updated successfully!";
                // Re-fetch data to display updated values
                $result_fetch_updated = mysqli_query($conn, $sql_fetch);
                if ($result_fetch_updated && mysqli_num_rows($result_fetch_updated) == 1) {
                    $photo = mysqli_fetch_assoc($result_fetch_updated);
                }
                mysqli_free_result($result_fetch_updated);

            } else {
                $error_msg = "Error updating photo: " . mysqli_error($conn);
            }
        }
    }
}

?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?php echo $page_title; ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Admin</a></li>
                    <li class="breadcrumb-item"><a href="manage_photos.php">Photos</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Update Photo Details</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $photo_id; ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">

                    <div class="form-group">
                        <label for="title">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($photo['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($photo['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="gallery_tag">Gallery Tag (Optional)</label>
                        <input type="text" class="form-control" id="gallery_tag" name="gallery_tag" placeholder="e.g., Event2023, Nature" value="<?php echo htmlspecialchars($photo['gallery_tag']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Current Image</label><br>
                        <?php if (!empty($photo['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($photo['image_url']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>" style="max-width: 200px; max-height: 200px; border-radius: 5px; margin-bottom:10px;">
                        <?php else: ?>
                            <p>No current image.</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="new_image">Replace Image (Optional - Max 10MB: JPG, JPEG, PNG, GIF, WEBP)</label>
                        <input type="file" class="form-control-file" id="new_image" name="new_image" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small class="form-text text-muted">If you upload a new image, the current one will be replaced.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Photo</button>
                    <a href="manage_photos.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
