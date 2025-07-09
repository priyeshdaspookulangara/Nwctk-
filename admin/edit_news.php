<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Edit News/Event";
$msg = "";
$error_msg = "";
$item_id = null;
$item = null;

// Define upload directory
$upload_dir_server = "../public/uploads/news_events/";
$upload_dir_web = "../public/uploads/news_events/";

// Get item ID from URL
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $item_id = $_GET['id'];
} else {
    header("Location: manage_news.php?error=Invalid Item ID");
    exit;
}

// Fetch existing item data
$sql_fetch = "SELECT id, title, content, image_url, date, type FROM news_events WHERE id = " . $item_id;
$result_fetch = mysqli_query($conn, $sql_fetch);
if ($result_fetch && mysqli_num_rows($result_fetch) == 1) {
    $item = mysqli_fetch_assoc($result_fetch);
} else {
    header("Location: manage_news.php?error=Item not found");
    exit;
}
mysqli_free_result($result_fetch);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $posted_item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
    if ($posted_item_id !== $item_id) {
        $error_msg = "Error: Item ID mismatch.";
    } else {
        $title = sanitize_input($conn, $_POST['title']);
        $content = sanitize_input($conn, $_POST['content']);
        $date = sanitize_input($conn, $_POST['date']);
        $type = sanitize_input($conn, $_POST['type']);

        $current_image_url = $item['image_url'];

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

        if (empty($title)) {
            $error_msg .= "Title is required. ";
        }
        if (empty($content)) {
            $error_msg .= "Content is required. ";
        }
        if (empty($date)) {
            $error_msg .= "Date is required. ";
        }
        if (!in_array($type, ['news', 'event'])) {
            $error_msg .= "Invalid type selected. ";
        }

        if (empty($error_msg)) {
            // $formatted_date = date('Y-m-d', strtotime($date));
            $sql_update = "UPDATE news_events SET
                title = '" . $title . "',
                content = '" . $content . "',
                image_url = '" . $current_image_url . "',
                date = '" . $date . "',
                type = '" . $type . "'
                WHERE id = " . $item_id;

            if (mysqli_query($conn, $sql_update)) {
                $msg = ucfirst($type) . " details updated successfully!";
                // Re-fetch data
                $result_fetch_updated = mysqli_query($conn, $sql_fetch);
                if ($result_fetch_updated && mysqli_num_rows($result_fetch_updated) == 1) {
                    $item = mysqli_fetch_assoc($result_fetch_updated);
                }
                mysqli_free_result($result_fetch_updated);
            } else {
                $error_msg = "Error updating item: " . mysqli_error($conn);
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
                <h1 class="m-0"><?php echo $page_title; ?>: <?php echo htmlspecialchars($item['title']); ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Admin</a></li>
                    <li class="breadcrumb-item"><a href="manage_news.php">News & Events</a></li>
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
                <h3 class="card-title">Update Item Details</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $item_id; ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">

                    <div class="form-group">
                        <label for="title">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($item['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="content">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($item['content']); ?></textarea>
                        <small class="form-text text-muted">Basic HTML is allowed.</small>
                    </div>

                    <div class="form-group">
                        <label for="date">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($item['date']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="type">Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="type" name="type" required>
                            <option value="news" <?php echo ($item['type'] == 'news') ? 'selected' : ''; ?>>News</option>
                            <option value="event" <?php echo ($item['type'] == 'event') ? 'selected' : ''; ?>>Event</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="image">New Image (Optional - Max 5MB: JPG, JPEG, PNG, GIF)</label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                        <?php if (!empty($item['image_url'])): ?>
                            <p class="mt-2">
                                <small>Current Image:</small><br>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" style="max-width: 150px; max-height: 150px; border-radius: 5px;">
                            </p>
                        <?php else: ?>
                             <p class="mt-2"><small>No current image.</small></p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Item</button>
                    <a href="manage_news.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
