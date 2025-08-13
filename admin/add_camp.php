<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Add New Camp";
$msg = "";
$error_msg = "";

// Define upload directory
$upload_dir_server = "../public/uploads/camps/";
$upload_dir_web = "../public/uploads/camps/";

// Create directory if it doesn't exist
if (!is_dir($upload_dir_server)) {
    mkdir($upload_dir_server, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize_input($conn, $_POST['name']);
    $description = sanitize_input($conn, $_POST['description']);
    $location = sanitize_input($conn, $_POST['location']);
    $start_date = !empty($_POST['start_date']) ? sanitize_input($conn, $_POST['start_date']) : NULL;
    $end_date = !empty($_POST['end_date']) ? sanitize_input($conn, $_POST['end_date']) : NULL;

    $image_url = "";

    // Image Upload Handling
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = basename($_FILES["image"]["name"]);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $target_file_server = $upload_dir_server . uniqid() . '_' . $file_name;
        $target_file_web = str_replace('../public/', '../public/', $target_file_server);

        if (in_array($file_type, $allowed_types)) {
            if ($_FILES["image"]["size"] < 5000000) { // 5MB limit
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file_server)) {
                    $image_url = $target_file_web;
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
        $error_msg .= "Camp name is required. ";
    }
    if ($start_date && $end_date && strtotime($end_date) < strtotime($start_date)) {
        $error_msg .= "End date cannot be before start date. ";
    }

    if (empty($error_msg)) {
        $start_date_sql = $start_date ? "'" . $start_date . "'" : "NULL";
        $end_date_sql = $end_date ? "'" . $end_date . "'" : "NULL";

        $sql = "INSERT INTO camps (name, description, location, start_date, end_date, image_url) VALUES (
                '" . $name . "',
                '" . $description . "',
                '" . $location . "',
                " . $start_date_sql . ",
                " . $end_date_sql . ",
                '" . $image_url . "'
            )";

        if (mysqli_query($conn, $sql)) {
            $msg = "New camp added successfully!";
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
                    <li class="breadcrumb-item"><a href="manage_camps.php">Camps</a></li>
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
                <h3 class="card-title">Camp Details</h3>
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
                        <label for="name">Camp Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" class="form-control" id="location" name="location" value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">Camp Image (Optional, Max 5MB: JPG, JPEG, PNG, GIF)</label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                    </div>

                    <button type="submit" class="btn btn-primary">Add Camp</button>
                    <a href="manage_camps.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
