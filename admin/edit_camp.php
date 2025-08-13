<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Edit Camp";
$msg = "";
$error_msg = "";
$camp_id = null;
$camp = null;

// Define upload directory
$upload_dir_server = "../public/uploads/camps/";
$upload_dir_web = "../public/uploads/camps/";

// Get camp ID from URL
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $camp_id = $_GET['id'];
} else {
    header("Location: manage_camps.php?error=Invalid Camp ID");
    exit;
}

// Fetch existing camp data
$sql_fetch = "SELECT id, name, description, location, start_date, end_date, image_url FROM camps WHERE id = " . $camp_id;
$result_fetch = mysqli_query($conn, $sql_fetch);
if ($result_fetch && mysqli_num_rows($result_fetch) == 1) {
    $camp = mysqli_fetch_assoc($result_fetch);
} else {
    header("Location: manage_camps.php?error=Camp not found");
    exit;
}
mysqli_free_result($result_fetch);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $posted_camp_id = filter_input(INPUT_POST, 'camp_id', FILTER_VALIDATE_INT);
    if ($posted_camp_id !== $camp_id) {
        $error_msg = "Error: Camp ID mismatch.";
    } else {
        $name = sanitize_input($conn, $_POST['name']);
        $description = sanitize_input($conn, $_POST['description']);
        $location = sanitize_input($conn, $_POST['location']);
        $start_date = !empty($_POST['start_date']) ? sanitize_input($conn, $_POST['start_date']) : NULL;
        $end_date = !empty($_POST['end_date']) ? sanitize_input($conn, $_POST['end_date']) : NULL;

        $current_image_url = $camp['image_url'];

        // Image Upload Handling (if new image provided)
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $file_name = basename($_FILES["image"]["name"]);
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if (!is_dir($upload_dir_server)) {
                mkdir($upload_dir_server, 0777, true);
            }
            $target_file_server = $upload_dir_server . uniqid() . '_' . $file_name;
            $target_file_web = str_replace('../public/', '../public/', $target_file_server);

            if (in_array($file_type, $allowed_types)) {
                if ($_FILES["image"]["size"] < 5000000) { // 5MB limit
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file_server)) {
                        if (!empty($current_image_url)) {
                            $old_image_server_path = realpath(dirname(__FILE__) . '/' . $current_image_url);
                            if ($old_image_server_path && file_exists($old_image_server_path)) {
                                unlink($old_image_server_path);
                            }
                        }
                        $current_image_url = $target_file_web;
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
            $error_msg .= "Camp name is required. ";
        }
        if ($start_date && $end_date && strtotime($end_date) < strtotime($start_date)) {
            $error_msg .= "End date cannot be before start date. ";
        }

        if (empty($error_msg)) {
            $start_date_sql = $start_date ? "'" . $start_date . "'" : "NULL";
            $end_date_sql = $end_date ? "'" . $end_date . "'" : "NULL";

            $sql_update = "UPDATE camps SET
                name = '" . $name . "',
                description = '" . $description . "',
                location = '" . $location . "',
                start_date = " . $start_date_sql . ",
                end_date = " . $end_date_sql . ",
                image_url = '" . $current_image_url . "'
                WHERE id = " . $camp_id;

            if (mysqli_query($conn, $sql_update)) {
                $msg = "Camp details updated successfully!";
                // Re-fetch data
                $result_fetch_updated = mysqli_query($conn, $sql_fetch);
                if ($result_fetch_updated && mysqli_num_rows($result_fetch_updated) == 1) {
                    $camp = mysqli_fetch_assoc($result_fetch_updated);
                }
                mysqli_free_result($result_fetch_updated);
            } else {
                $error_msg = "Error updating camp: " . mysqli_error($conn);
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
                <h1 class="m-0"><?php echo $page_title; ?>: <?php echo htmlspecialchars($camp['name']); ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Admin</a></li>
                    <li class="breadcrumb-item"><a href="manage_camps.php">Camps</a></li>
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
                <h3 class="card-title">Update Camp Details</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $camp_id; ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="camp_id" value="<?php echo $camp['id']; ?>">

                    <div class="form-group">
                        <label for="name">Camp Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($camp['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($camp['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($camp['location']); ?>">
                    </div>

                     <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($camp['start_date']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($camp['end_date']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">New Camp Image (Optional - Max 5MB: JPG, JPEG, PNG, GIF)</label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                        <?php if (!empty($camp['image_url'])): ?>
                            <p class="mt-2">
                                <small>Current Image:</small><br>
                                <img src="<?php echo htmlspecialchars($camp['image_url']); ?>" alt="<?php echo htmlspecialchars($camp['name']); ?>" style="max-width: 150px; max-height: 150px; border-radius: 5px;">
                            </p>
                        <?php else: ?>
                             <p class="mt-2"><small>No current image.</small></p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Camp</button>
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
