<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Add New Banner";
$msg = "";
$error_msg = "";

$upload_dir_server = "../public/uploads/banners/";
if (!is_dir($upload_dir_server)) {
    mkdir($upload_dir_server, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $heading = sanitize_input($conn, $_POST['heading']);
    $sub_heading = sanitize_input($conn, $_POST['sub_heading']);
    $link = sanitize_input($conn, $_POST['link']);
    $display_order = filter_input(INPUT_POST, 'display_order', FILTER_VALIDATE_INT, ["options" => ["default" => 0]]);
    $image_url = "";

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = basename($_FILES["image"]["name"]);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $target_file_server = $upload_dir_server . uniqid() . '_' . $file_name;

        if (in_array($file_type, $allowed_types)) {
            if ($_FILES["image"]["size"] < 5000000) { // 5MB limit
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file_server)) {
                    $image_url = str_replace('../public/', '', $target_file_server);
                } else {
                    $error_msg .= "Sorry, there was an error uploading your file. ";
                }
            } else {
                $error_msg .= "Sorry, your file is too large. Max 5MB. ";
            }
        } else {
            $error_msg .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed. ";
        }
    } else {
        $error_msg .= "Banner image is required. ";
    }

    if (empty($heading)) {
        $error_msg .= "Heading is required. ";
    }

    if (empty($error_msg)) {
        $sql = "INSERT INTO banners (heading, sub_heading, link, image_url, display_order) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $heading, $sub_heading, $link, $image_url, $display_order);

        if (mysqli_stmt_execute($stmt)) {
            $msg = "New banner added successfully!";
            $_POST = array();
        } else {
            $error_msg = "Error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
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
                    <li class="breadcrumb-item"><a href="manage_banners.php">Banners</a></li>
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
                <h3 class="card-title">Banner Details</h3>
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
                        <label for="heading">Heading <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="heading" name="heading" required>
                    </div>
                    <div class="form-group">
                        <label for="sub_heading">Sub-Heading</label>
                        <input type="text" class="form-control" id="sub_heading" name="sub_heading">
                    </div>
                    <div class="form-group">
                        <label for="link">Link</label>
                        <input type="text" class="form-control" id="link" name="link">
                    </div>
                    <div class="form-group">
                        <label for="image">Image (Max 5MB: JPG, JPEG, PNG, GIF) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/gif" required>
                    </div>
                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" class="form-control" id="display_order" name="display_order" value="0">
                        <small class="form-text text-muted">Lower numbers display first.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Banner</button>
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
