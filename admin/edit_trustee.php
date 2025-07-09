<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Edit Trustee";
$msg = "";
$error_msg = "";
$trustee_id = null;
$trustee = null;

// Define upload directory (consistent with add_trustee.php)
$upload_dir_server = "../public/uploads/trustees/";
$upload_dir_web = "../public/uploads/trustees/";

// Get trustee ID from URL
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $trustee_id = $_GET['id'];
} else {
    // No ID or invalid ID, redirect or show error
    header("Location: manage_trustees.php?error=Invalid Trustee ID");
    exit;
}

// Fetch existing trustee data
$sql_fetch = "SELECT id, name, position, bio, image_url, display_order FROM trustees WHERE id = " . $trustee_id;
$result_fetch = mysqli_query($conn, $sql_fetch);
if ($result_fetch && mysqli_num_rows($result_fetch) == 1) {
    $trustee = mysqli_fetch_assoc($result_fetch);
} else {
    header("Location: manage_trustees.php?error=Trustee not found");
    exit;
}
mysqli_free_result($result_fetch);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure trustee_id from POST matches the one from GET to prevent manipulation
    $posted_trustee_id = filter_input(INPUT_POST, 'trustee_id', FILTER_VALIDATE_INT);
    if ($posted_trustee_id !== $trustee_id) {
        $error_msg = "Error: Trustee ID mismatch.";
        // Log this potential tampering attempt
    } else {
        $name = sanitize_input($conn, $_POST['name']);
        $position = sanitize_input($conn, $_POST['position']);
        $bio = sanitize_input($conn, $_POST['bio']);
        $display_order = filter_input(INPUT_POST, 'display_order', FILTER_VALIDATE_INT, ["options" => ["default" => 0]]);

        $current_image_url = $trustee['image_url']; // Existing image

        // Image Upload Handling (if a new image is provided)
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $file_name = basename($_FILES["image"]["name"]);
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            // Ensure upload directory exists
            if (!is_dir($upload_dir_server)) {
                mkdir($upload_dir_server, 0777, true);
            }
            $target_file_server = $upload_dir_server . uniqid() . '_' . $file_name;
            $target_file_web = str_replace('../public/', '../public/', $target_file_server);

            if (in_array($file_type, $allowed_types)) {
                if ($_FILES["image"]["size"] < 5000000) { // 5MB limit
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file_server)) {
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
                    $error_msg .= "Sorry, your new file is too large. Max 5MB. ";
                }
            } else {
                $error_msg .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed for new image. ";
            }
        } elseif (isset($_FILES["image"]) && $_FILES["image"]["error"] != UPLOAD_ERR_NO_FILE) {
             $error_msg .= "Error during new file upload: " . $_FILES["image"]["error"] . ". ";
        }


        if (empty($name)) {
            $error_msg .= "Trustee name is required. ";
        }

        if (empty($error_msg)) {
            $sql_update = "UPDATE trustees SET
                name = '" . $name . "',
                position = '" . $position . "',
                bio = '" . $bio . "',
                image_url = '" . $current_image_url . "',
                display_order = " . $display_order . "
                WHERE id = " . $trustee_id;

            if (mysqli_query($conn, $sql_update)) {
                $msg = "Trustee details updated successfully!";
                // Re-fetch data to display updated values in the form
                $result_fetch_updated = mysqli_query($conn, $sql_fetch); // $sql_fetch is still defined
                if ($result_fetch_updated && mysqli_num_rows($result_fetch_updated) == 1) {
                    $trustee = mysqli_fetch_assoc($result_fetch_updated);
                }
                mysqli_free_result($result_fetch_updated);

            } else {
                $error_msg = "Error updating trustee: " . mysqli_error($conn);
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
                <h1 class="m-0"><?php echo $page_title; ?>: <?php echo htmlspecialchars($trustee['name']); ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Admin</a></li>
                    <li class="breadcrumb-item"><a href="manage_trustees.php">Trustees</a></li>
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
                <h3 class="card-title">Update Trustee Details</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $trustee_id; ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="trustee_id" value="<?php echo $trustee['id']; ?>">

                    <div class="form-group">
                        <label for="name">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($trustee['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" class="form-control" id="position" name="position" value="<?php echo htmlspecialchars($trustee['position']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="bio">Biography</label>
                        <textarea class="form-control" id="bio" name="bio" rows="5"><?php echo htmlspecialchars($trustee['bio']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">New Image (Optional - Max 5MB: JPG, JPEG, PNG, GIF)</label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                        <?php if (!empty($trustee['image_url'])): ?>
                            <p class="mt-2">
                                <small>Current Image:</small><br>
                                <img src="<?php echo htmlspecialchars($trustee['image_url']); ?>" alt="<?php echo htmlspecialchars($trustee['name']); ?>" style="max-width: 150px; max-height: 150px; border-radius: 5px;">
                            </p>
                        <?php else: ?>
                            <p class="mt-2"><small>No current image.</small></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo htmlspecialchars($trustee['display_order']); ?>">
                        <small class="form-text text-muted">Lower numbers display first.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Trustee</button>
                    <a href="manage_trustees.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
