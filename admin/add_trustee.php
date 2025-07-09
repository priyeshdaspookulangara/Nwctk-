<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Add New Trustee";
$msg = "";
$error_msg = "";

// Define upload directory relative to the public folder for web access,
// and server path for file operations.
$upload_dir_server = "../public/uploads/trustees/";
$upload_dir_web = "../public/uploads/trustees/"; // Path for img src

// Create directory if it doesn't exist
if (!is_dir($upload_dir_server)) {
    mkdir($upload_dir_server, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize_input($conn, $_POST['name']);
    $position = sanitize_input($conn, $_POST['position']);
    $bio = sanitize_input($conn, $_POST['bio']);
    $display_order = filter_input(INPUT_POST, 'display_order', FILTER_VALIDATE_INT, ["options" => ["default" => 0]]);

    $image_url = ""; // Initialize image_url

    // Image Upload Handling
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = basename($_FILES["image"]["name"]);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $target_file_server = $upload_dir_server . uniqid() . '_' . $file_name;
        $target_file_web = str_replace('../public/', '../public/', $target_file_server); // Path for DB

        // Check file type
        if (in_array($file_type, $allowed_types)) {
            // Check file size (e.g., 5MB limit)
            if ($_FILES["image"]["size"] < 5000000) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file_server)) {
                    $image_url = $target_file_web; // Store web-accessible path
                } else {
                    $error_msg .= "Sorry, there was an error uploading your file. ";
                }
            } else {
                $error_msg .= "Sorry, your file is too large. Max 5MB. ";
            }
        } else {
            $error_msg .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed. ";
        }
    } elseif (isset($_FILES["image"]) && $_FILES["image"]["error"] != UPLOAD_ERR_NO_FILE) {
        $error_msg .= "Error during file upload: " . $_FILES["image"]["error"] . ". ";
    }

    if (empty($name)) {
        $error_msg .= "Trustee name is required. ";
    }

    if (empty($error_msg)) {
        $sql = "INSERT INTO trustees (name, position, bio, image_url, display_order) VALUES (
                '" . $name . "',
                '" . $position . "',
                '" . $bio . "',
                '" . $image_url . "',
                " . $display_order . "
            )";

        if (mysqli_query($conn, $sql)) {
            $msg = "New trustee added successfully!";
            // Clear form fields after successful submission (optional)
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
                    <li class="breadcrumb-item"><a href="manage_trustees.php">Trustees</a></li>
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
                <h3 class="card-title">Trustee Details</h3>
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
                        <label for="name">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" class="form-control" id="position" name="position" value="<?php echo isset($_POST['position']) ? htmlspecialchars($_POST['position']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="bio">Biography</label>
                        <textarea class="form-control" id="bio" name="bio" rows="5"><?php echo isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">Image (Max 5MB: JPG, JPEG, PNG, GIF)</label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                    </div>

                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo isset($_POST['display_order']) ? htmlspecialchars($_POST['display_order']) : '0'; ?>">
                        <small class="form-text text-muted">Lower numbers display first.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Trustee</button>
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
