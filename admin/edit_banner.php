<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Edit Banner";
$msg = "";
$error_msg = "";
$banner_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$banner = null;

if (!$banner_id) {
    header("Location: manage_banners.php?error=Invalid Banner ID");
    exit;
}

// Fetch existing banner data
$sql_fetch = "SELECT id, heading, sub_heading, link, image_url, display_order FROM banners WHERE id = ?";
$stmt_fetch = mysqli_prepare($conn, $sql_fetch);
mysqli_stmt_bind_param($stmt_fetch, "i", $banner_id);
mysqli_stmt_execute($stmt_fetch);
$result_fetch = mysqli_stmt_get_result($stmt_fetch);
if ($banner = mysqli_fetch_assoc($result_fetch)) {
    // Banner found
} else {
    header("Location: manage_banners.php?error=Banner not found");
    exit;
}
mysqli_stmt_close($stmt_fetch);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $heading = sanitize_input($conn, $_POST['heading']);
    $sub_heading = sanitize_input($conn, $_POST['sub_heading']);
    $link = sanitize_input($conn, $_POST['link']);
    $display_order = filter_input(INPUT_POST, 'display_order', FILTER_VALIDATE_INT, ["options" => ["default" => 0]]);
    $current_image_url = $banner['image_url'];

    // Image Upload Handling
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $upload_dir_server = "../public/uploads/banners/";
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = basename($_FILES["image"]["name"]);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $target_file_server = $upload_dir_server . uniqid() . '_' . $file_name;

        if (in_array($file_type, $allowed_types)) {
            if ($_FILES["image"]["size"] < 5000000) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file_server)) {
                    // Delete old image
                    $old_image_path = "../public/" . $current_image_url;
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                    $current_image_url = str_replace('../public/', '', $target_file_server);
                } else {
                    $error_msg .= "Sorry, there was an error uploading your new file. ";
                }
            } else {
                $error_msg .= "Sorry, your new file is too large. Max 5MB. ";
            }
        } else {
            $error_msg .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed. ";
        }
    }

    if (empty($heading)) {
        $error_msg .= "Heading is required. ";
    }

    if (empty($error_msg)) {
        $sql_update = "UPDATE banners SET heading = ?, sub_heading = ?, link = ?, image_url = ?, display_order = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "ssssii", $heading, $sub_heading, $link, $current_image_url, $display_order, $banner_id);

        if (mysqli_stmt_execute($stmt_update)) {
            $msg = "Banner details updated successfully!";
            // Re-fetch data
            $stmt_refetch = mysqli_prepare($conn, $sql_fetch);
            mysqli_stmt_bind_param($stmt_refetch, "i", $banner_id);
            mysqli_stmt_execute($stmt_refetch);
            $result_refetch = mysqli_stmt_get_result($stmt_refetch);
            $banner = mysqli_fetch_assoc($result_refetch);
            mysqli_stmt_close($stmt_refetch);
        } else {
            $error_msg = "Error updating banner: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt_update);
    }
}

?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><?php echo $page_title; ?></h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Admin</a></li>
                    <li class="breadcrumb-item"><a href="manage_banners.php">Banners</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Update Banner Details</h3></div>
            <div class="card-body">
                <?php if ($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
                <?php if ($error_msg) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>

                <form action="?id=<?php echo $banner_id; ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="heading">Heading <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="heading" name="heading" value="<?php echo htmlspecialchars($banner['heading']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="sub_heading">Sub-Heading</label>
                        <input type="text" class="form-control" id="sub_heading" name="sub_heading" value="<?php echo htmlspecialchars($banner['sub_heading']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="link">Link</label>
                        <input type="text" class="form-control" id="link" name="link" value="<?php echo htmlspecialchars($banner['link']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="image">New Image (Optional)</label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                        <p class="mt-2"><small>Current Image:</small><br><img src="../public/<?php echo htmlspecialchars($banner['image_url']); ?>" alt="" style="max-width: 200px;"></p>
                    </div>
                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo htmlspecialchars($banner['display_order']); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Banner</button>
                    <a href="manage_banners.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
