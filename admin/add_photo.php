<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Add New Photo";
$msg = "";
$error_msg = "";

// Define upload directory
$upload_dir_server = "../public/uploads/photos/";
$upload_dir_web = "../public/uploads/photos/";

// Create directory if it doesn't exist
if (!is_dir($upload_dir_server)) {
    mkdir($upload_dir_server, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = sanitize_input($conn, $_POST['title']);
    $description = sanitize_input($conn, $_POST['description']);
    $gallery_tag = sanitize_input($conn, $_POST['gallery_tag']); // Optional

    $image_url = "";

    // Image Upload Handling - This is mandatory for adding a photo
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp']; // Added webp
        $file_name = basename($_FILES["image"]["name"]);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $target_file_server = $upload_dir_server . uniqid() . '_' . $file_name;
        $target_file_web = str_replace('../public/', '../public/', $target_file_server);

        if (in_array($file_type, $allowed_types)) {
            if ($_FILES["image"]["size"] < 10000000) { // 10MB limit for photos
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file_server)) {
                    $image_url = $target_file_web;
                } else {
                    $error_msg .= "Sorry, there was an error uploading your file. ";
                }
            } else {
                $error_msg .= "Sorry, your file is too large. Max 10MB. ";
            }
        } else {
            $error_msg .= "Sorry, only JPG, JPEG, PNG, GIF & WEBP files are allowed. ";
        }
    } else {
        // Photo file is mandatory for adding a new photo
        $error_msg .= "A photo file is required. Error: " . (isset($_FILES["image"]["error"]) ? $_FILES["image"]["error"] : "No file uploaded") . ". ";
    }

    if (empty($title)) {
        $error_msg .= "Photo title is required. ";
    }

    if (empty($error_msg) && !empty($image_url)) { // Ensure image was uploaded
        $sql = "INSERT INTO photos (title, description, image_url, gallery_tag) VALUES (
                '" . $title . "',
                '" . $description . "',
                '" . $image_url . "',
                '" . $gallery_tag . "'
            )";

        if (mysqli_query($conn, $sql)) {
            $msg = "New photo added successfully!";
            $_POST = array();
        } else {
            $error_msg = "Error: " . mysqli_error($conn);
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
                    <li class="breadcrumb-item active">Add New</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Photo Details</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="gallery_tag">Gallery Tag (Optional)</label>
                        <input type="text" class="form-control" id="gallery_tag" name="gallery_tag" placeholder="e.g., Event2023, Nature" value="<?php echo isset($_POST['gallery_tag']) ? htmlspecialchars($_POST['gallery_tag']) : ''; ?>">
                        <small class="form-text text-muted">Use tags to group photos into galleries.</small>
                    </div>

                    <div class="form-group">
                        <label for="image">Photo File <span class="text-danger">*</span> (Max 10MB: JPG, JPEG, PNG, GIF, WEBP)</label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Photo</button>
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
