<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Add New News/Event";
$msg = "";
$error_msg = "";

// Define upload directory
$upload_dir_server = "../public/uploads/news_events/";
$upload_dir_web = "../public/uploads/news_events/"; // Path for img src

// Create directory if it doesn't exist
if (!is_dir($upload_dir_server)) {
    mkdir($upload_dir_server, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = sanitize_input($conn, $_POST['title']);
    $content = sanitize_input($conn, $_POST['content']); // Basic sanitization, consider a more robust HTML purifier if allowing HTML
    $date = sanitize_input($conn, $_POST['date']); // Expects YYYY-MM-DD
    $type = sanitize_input($conn, $_POST['type']); // 'news' or 'event'

    $image_url = ""; // Initialize image_url

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
        // Format date for SQL if it's not already YYYY-MM-DD. Assuming HTML date input provides this.
        // $formatted_date = date('Y-m-d', strtotime($date)); // Use if input date format varies

        $sql = "INSERT INTO news_events (title, content, image_url, date, type) VALUES (
                '" . $title . "',
                '" . $content . "',
                '" . $image_url . "',
                '" . $date . "',
                '" . $type . "'
            )";

        if (mysqli_query($conn, $sql)) {
            $msg = "New " . ucfirst($type) . " added successfully!";
            $_POST = array(); // Clear form
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
                    <li class="breadcrumb-item"><a href="manage_news.php">News & Events</a></li>
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
                <h3 class="card-title">Item Details</h3>
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
                        <label for="content">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="10" required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                        <small class="form-text text-muted">Basic HTML is allowed. For complex content, consider a WYSIWYG editor integration (not implemented here).</small>
                    </div>

                    <div class="form-group">
                        <label for="date">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="type">Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="type" name="type" required>
                            <option value="news" <?php echo (isset($_POST['type']) && $_POST['type'] == 'news') ? 'selected' : ''; ?>>News</option>
                            <option value="event" <?php echo (isset($_POST['type']) && $_POST['type'] == 'event') ? 'selected' : ''; ?>>Event</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="image">Image (Optional, Max 5MB: JPG, JPEG, PNG, GIF)</label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                    </div>

                    <button type="submit" class="btn btn-primary">Add Item</button>
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
