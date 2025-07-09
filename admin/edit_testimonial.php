<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Edit Testimonial";
$msg = "";
$error_msg = "";
$testimonial_id = null;
$testimonial = null;

// Define upload directory
$upload_dir_server = "../public/uploads/testimonials/";
$upload_dir_web = "../public/uploads/testimonials/";

// Get testimonial ID from URL
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $testimonial_id = $_GET['id'];
} else {
    header("Location: manage_testimonials.php?error=Invalid Testimonial ID");
    exit;
}

// Fetch existing testimonial data
$sql_fetch = "SELECT id, author_name, author_position, testimonial_text, image_url, rating FROM testimonials WHERE id = " . $testimonial_id;
$result_fetch = mysqli_query($conn, $sql_fetch);
if ($result_fetch && mysqli_num_rows($result_fetch) == 1) {
    $testimonial = mysqli_fetch_assoc($result_fetch);
} else {
    header("Location: manage_testimonials.php?error=Testimonial not found");
    exit;
}
mysqli_free_result($result_fetch);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $posted_testimonial_id = filter_input(INPUT_POST, 'testimonial_id', FILTER_VALIDATE_INT);
    if ($posted_testimonial_id !== $testimonial_id) {
        $error_msg = "Error: Testimonial ID mismatch.";
    } else {
        $author_name = sanitize_input($conn, $_POST['author_name']);
        $author_position = sanitize_input($conn, $_POST['author_position']);
        $testimonial_text = sanitize_input($conn, $_POST['testimonial_text']);
        $rating = isset($_POST['rating']) && !empty($_POST['rating']) ? filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]]) : NULL;

        $current_image_url = $testimonial['image_url'];

        // Image Upload Handling (if new image provided)
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
                if ($_FILES["new_image"]["size"] < 2000000) { // 2MB limit
                    if (move_uploaded_file($_FILES["new_image"]["tmp_name"], $target_file_server)) {
                        if (!empty($current_image_url)) {
                            $old_image_server_path = realpath(dirname(__FILE__) . '/' . $current_image_url);
                            if ($old_image_server_path && file_exists($old_image_server_path)) {
                                unlink($old_image_server_path);
                            }
                        }
                        $current_image_url = $target_file_web;
                    } else {
                        $error_msg .= "Sorry, there was an error uploading the new author image. ";
                    }
                } else {
                    $error_msg .= "Sorry, the new author image file is too large. Max 2MB. ";
                }
            } else {
                $error_msg .= "Sorry, only JPG, JPEG, PNG, GIF & WEBP files are allowed for the new author image. ";
            }
        } elseif (isset($_FILES["new_image"]) && $_FILES["new_image"]["error"] != UPLOAD_ERR_NO_FILE) {
             $error_msg .= "Error during new author image upload: " . $_FILES["new_image"]["error"] . ". ";
        }

        if (empty($author_name)) {
            $error_msg .= "Author name is required. ";
        }
        if (empty($testimonial_text)) {
            $error_msg .= "Testimonial text is required. ";
        }
        if (isset($_POST['rating']) && !empty($_POST['rating']) && $rating === false && $_POST['rating'] !== '0') {
            // Allow empty string for rating to be NULL, but not invalid numbers.
            $error_msg .= "Invalid rating value. Must be between 1 and 5, or leave blank. ";
        }


        if (empty($error_msg)) {
            $rating_sql = $rating !== NULL ? $rating : "NULL";
            $sql_update = "UPDATE testimonials SET
                author_name = '" . $author_name . "',
                author_position = '" . $author_position . "',
                testimonial_text = '" . $testimonial_text . "',
                image_url = '" . $current_image_url . "',
                rating = " . $rating_sql . "
                WHERE id = " . $testimonial_id;

            if (mysqli_query($conn, $sql_update)) {
                $msg = "Testimonial details updated successfully!";
                // Re-fetch data
                $result_fetch_updated = mysqli_query($conn, $sql_fetch);
                if ($result_fetch_updated && mysqli_num_rows($result_fetch_updated) == 1) {
                    $testimonial = mysqli_fetch_assoc($result_fetch_updated);
                }
                mysqli_free_result($result_fetch_updated);
            } else {
                $error_msg = "Error updating testimonial: " . mysqli_error($conn);
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
                <h1 class="m-0"><?php echo $page_title; ?>: <?php echo htmlspecialchars($testimonial['author_name']); ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Admin</a></li>
                    <li class="breadcrumb-item"><a href="manage_testimonials.php">Testimonials</a></li>
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
                <h3 class="card-title">Update Testimonial Details</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $testimonial_id; ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">

                    <div class="form-group">
                        <label for="author_name">Author's Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="author_name" name="author_name" value="<?php echo htmlspecialchars($testimonial['author_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="author_position">Author's Position</label>
                        <input type="text" class="form-control" id="author_position" name="author_position" value="<?php echo htmlspecialchars($testimonial['author_position']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="testimonial_text">Testimonial Text <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="testimonial_text" name="testimonial_text" rows="5" required><?php echo htmlspecialchars($testimonial['testimonial_text']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="rating">Rating (Optional, 1-5 Stars)</label>
                        <input type="number" class="form-control" id="rating" name="rating" min="1" max="5" value="<?php echo htmlspecialchars($testimonial['rating'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Current Author Image</label><br>
                        <?php if (!empty($testimonial['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($testimonial['image_url']); ?>" alt="<?php echo htmlspecialchars($testimonial['author_name']); ?>" style="max-width: 100px; max-height: 100px; border-radius: 50%; margin-bottom:10px;">
                        <?php else: ?>
                            <p>No current image.</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="new_image">Replace Author Image (Optional - Max 2MB)</label>
                        <input type="file" class="form-control-file" id="new_image" name="new_image" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small class="form-text text-muted">If you upload a new image, the current one will be replaced.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Testimonial</button>
                    <a href="manage_testimonials.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
