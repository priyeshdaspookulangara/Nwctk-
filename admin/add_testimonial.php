<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Add New Testimonial";
$msg = "";
$error_msg = "";

// Define upload directory
$upload_dir_server = "../public/uploads/testimonials/";
$upload_dir_web = "../public/uploads/testimonials/";

// Create directory if it doesn't exist
if (!is_dir($upload_dir_server)) {
    mkdir($upload_dir_server, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $author_name = sanitize_input($conn, $_POST['author_name']);
    $author_position = sanitize_input($conn, $_POST['author_position']);
    $testimonial_text = sanitize_input($conn, $_POST['testimonial_text']);
    $rating = isset($_POST['rating']) && !empty($_POST['rating']) ? filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]]) : NULL;

    $image_url = "";

    // Image Upload Handling (Optional for author photo)
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_name = basename($_FILES["image"]["name"]);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $target_file_server = $upload_dir_server . uniqid() . '_' . $file_name;
        $target_file_web = str_replace('../public/', '../public/', $target_file_server);

        if (in_array($file_type, $allowed_types)) {
            if ($_FILES["image"]["size"] < 2000000) { // 2MB limit for author photos
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file_server)) {
                    $image_url = $target_file_web;
                } else {
                    $error_msg .= "Sorry, there was an error uploading the author's image. ";
                }
            } else {
                $error_msg .= "Sorry, the author's image file is too large. Max 2MB. ";
            }
        } else {
            $error_msg .= "Sorry, only JPG, JPEG, PNG, GIF & WEBP files are allowed for the author's image. ";
        }
    } elseif (isset($_FILES["image"]) && $_FILES["image"]["error"] != UPLOAD_ERR_NO_FILE) {
        $error_msg .= "Error during author image upload: " . $_FILES["image"]["error"] . ". ";
    }

    if (empty($author_name)) {
        $error_msg .= "Author name is required. ";
    }
    if (empty($testimonial_text)) {
        $error_msg .= "Testimonial text is required. ";
    }
    if (isset($_POST['rating']) && !empty($_POST['rating']) && $rating === false) { // Check if filter_input failed
        $error_msg .= "Invalid rating value. Must be between 1 and 5. ";
    }


    if (empty($error_msg)) {
        $rating_sql = $rating !== NULL ? $rating : "NULL";

        $sql = "INSERT INTO testimonials (author_name, author_position, testimonial_text, image_url, rating) VALUES (
                '" . $author_name . "',
                '" . $author_position . "',
                '" . $testimonial_text . "',
                '" . $image_url . "',
                " . $rating_sql . "
            )";

        if (mysqli_query($conn, $sql)) {
            $msg = "New testimonial added successfully!";
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
                    <li class="breadcrumb-item"><a href="manage_testimonials.php">Testimonials</a></li>
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
                <h3 class="card-title">Testimonial Details</h3>
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
                        <label for="author_name">Author's Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="author_name" name="author_name" value="<?php echo isset($_POST['author_name']) ? htmlspecialchars($_POST['author_name']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="author_position">Author's Position (e.g., Volunteer, Beneficiary)</label>
                        <input type="text" class="form-control" id="author_position" name="author_position" value="<?php echo isset($_POST['author_position']) ? htmlspecialchars($_POST['author_position']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="testimonial_text">Testimonial Text <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="testimonial_text" name="testimonial_text" rows="5" required><?php echo isset($_POST['testimonial_text']) ? htmlspecialchars($_POST['testimonial_text']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="rating">Rating (Optional, 1-5 Stars)</label>
                        <input type="number" class="form-control" id="rating" name="rating" min="1" max="5" value="<?php echo isset($_POST['rating']) ? htmlspecialchars($_POST['rating']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="image">Author's Image (Optional, Max 2MB: JPG, JPEG, PNG, GIF, WEBP)</label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>

                    <button type="submit" class="btn btn-primary">Add Testimonial</button>
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
