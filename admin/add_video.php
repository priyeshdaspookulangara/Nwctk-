<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Add New Video";
$msg = "";
$error_msg = "";

// For this module, we are primarily focusing on YouTube links.
// File uploads for videos are not implemented in this iteration for simplicity.
// $upload_dir_server = "../public/uploads/videos/";
// $upload_dir_web = "../public/uploads/videos/";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = sanitize_input($conn, $_POST['title']);
    $description = sanitize_input($conn, $_POST['description']);
    $video_url = sanitize_input($conn, $_POST['video_url']);
    $video_type = sanitize_input($conn, $_POST['video_type']); // e.g., 'youtube'
    // $thumbnail_url = sanitize_input($conn, $_POST['thumbnail_url']); // Optional, could be auto-generated for YouTube

    if (empty($title)) {
        $error_msg .= "Video title is required. ";
    }
    if (empty($video_url)) {
        $error_msg .= "Video URL is required. ";
    } else {
        // Basic URL validation
        if (!filter_var($video_url, FILTER_VALIDATE_URL)) {
            $error_msg .= "Invalid Video URL format. ";
        }
    }
    if (!in_array($video_type, ['youtube', 'vimeo', 'file', 'other'])) { // Added more types
        $error_msg .= "Invalid video type selected. ";
    }

    // Auto-generate YouTube thumbnail URL if type is YouTube and no explicit thumbnail provided
    $thumbnail_url_to_save = ''; // Initialize
    if ($video_type === 'youtube' && empty($_POST['thumbnail_url'])) {
        // Try to extract YouTube video ID and generate thumbnail URL
        // Example: https://www.youtube.com/watch?v=VIDEO_ID
        // Example: https://youtu.be/VIDEO_ID
        $video_id = '';
        if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $video_url, $matches)) {
            $video_id = $matches[1];
        } elseif (preg_match('/youtu\.be\/([^\&\?\/]+)/', $video_url, $matches)) {
            $video_id = $matches[1];
        }
        if ($video_id) {
            $thumbnail_url_to_save = 'https://img.youtube.com/vi/' . $video_id . '/mqdefault.jpg';
        }
    } elseif (!empty($_POST['thumbnail_url'])) {
        $thumbnail_url_to_save = sanitize_input($conn, $_POST['thumbnail_url']);
        if (!filter_var($thumbnail_url_to_save, FILTER_VALIDATE_URL)) {
             $error_msg .= "Invalid Thumbnail URL format. ";
        }
    }


    if (empty($error_msg)) {
        $sql = "INSERT INTO videos (title, description, video_url, video_type, thumbnail_url) VALUES (
                '" . $title . "',
                '" . $description . "',
                '" . $video_url . "',
                '" . $video_type . "',
                '" . $thumbnail_url_to_save . "'
            )";

        if (mysqli_query($conn, $sql)) {
            $msg = "New video added successfully!";
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
                    <li class="breadcrumb-item"><a href="manage_videos.php">Videos</a></li>
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
                <h3 class="card-title">Video Details</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="title">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="video_url">Video URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="video_url" name="video_url" placeholder="e.g., https://www.youtube.com/watch?v=your_video_id" value="<?php echo isset($_POST['video_url']) ? htmlspecialchars($_POST['video_url']) : ''; ?>" required>
                        <small class="form-text text-muted">Enter the full URL of the video (e.g., YouTube, Vimeo).</small>
                    </div>

                    <div class="form-group">
                        <label for="video_type">Video Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="video_type" name="video_type" required>
                            <option value="youtube" <?php echo (isset($_POST['video_type']) && $_POST['video_type'] == 'youtube') ? 'selected' : ''; ?>>YouTube</option>
                            <option value="vimeo" <?php echo (isset($_POST['video_type']) && $_POST['video_type'] == 'vimeo') ? 'selected' : ''; ?>>Vimeo</option>
                            <option value="other" <?php echo (isset($_POST['video_type']) && $_POST['video_type'] == 'other') ? 'selected' : ''; ?>>Other URL</option>
                            <!-- <option value="file" <?php // echo (isset($_POST['video_type']) && $_POST['video_type'] == 'file') ? 'selected' : ''; ?>>Uploaded File (Not implemented)</option> -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="thumbnail_url">Custom Thumbnail URL (Optional)</label>
                        <input type="url" class="form-control" id="thumbnail_url" name="thumbnail_url" placeholder="e.g., https://example.com/image.jpg" value="<?php echo isset($_POST['thumbnail_url']) ? htmlspecialchars($_POST['thumbnail_url']) : ''; ?>">
                        <small class="form-text text-muted">If embedding a YouTube video and this is left blank, a default thumbnail will be attempted.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Video</button>
                    <a href="manage_videos.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
