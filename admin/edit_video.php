<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Edit Video";
$msg = "";
$error_msg = "";
$video_id_get = null;
$video = null;

// Get video ID from URL
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $video_id_get = $_GET['id'];
} else {
    header("Location: manage_videos.php?error=Invalid Video ID");
    exit;
}

// Fetch existing video data
$sql_fetch = "SELECT id, title, description, video_url, video_type, thumbnail_url FROM videos WHERE id = " . $video_id_get;
$result_fetch = mysqli_query($conn, $sql_fetch);
if ($result_fetch && mysqli_num_rows($result_fetch) == 1) {
    $video = mysqli_fetch_assoc($result_fetch);
} else {
    header("Location: manage_videos.php?error=Video not found");
    exit;
}
mysqli_free_result($result_fetch);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $posted_video_id = filter_input(INPUT_POST, 'video_id', FILTER_VALIDATE_INT);
    if ($posted_video_id !== $video_id_get) {
        $error_msg = "Error: Video ID mismatch.";
    } else {
        $title = sanitize_input($conn, $_POST['title']);
        $description = sanitize_input($conn, $_POST['description']);
        $video_url = sanitize_input($conn, $_POST['video_url']);
        $video_type = sanitize_input($conn, $_POST['video_type']);
        $thumbnail_url_posted = sanitize_input($conn, $_POST['thumbnail_url']);

        if (empty($title)) {
            $error_msg .= "Video title is required. ";
        }
        if (empty($video_url)) {
            $error_msg .= "Video URL is required. ";
        } else {
            if (!filter_var($video_url, FILTER_VALIDATE_URL)) {
                $error_msg .= "Invalid Video URL format. ";
            }
        }
        if (!in_array($video_type, ['youtube', 'vimeo', 'file', 'other'])) {
            $error_msg .= "Invalid video type selected. ";
        }
        if (!empty($thumbnail_url_posted) && !filter_var($thumbnail_url_posted, FILTER_VALIDATE_URL)) {
            $error_msg .= "Invalid Custom Thumbnail URL format. ";
        }

        // Auto-generate YouTube thumbnail URL if type is YouTube and no explicit thumbnail provided or current one is empty
        $thumbnail_url_to_save = $thumbnail_url_posted; // Use posted one if available and valid
        if ($video_type === 'youtube' && empty($thumbnail_url_posted)) {
            $yt_video_id_extract = '';
            if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $video_url, $matches)) {
                $yt_video_id_extract = $matches[1];
            } elseif (preg_match('/youtu\.be\/([^\&\?\/]+)/', $video_url, $matches)) {
                $yt_video_id_extract = $matches[1];
            }
            if ($yt_video_id_extract) {
                $thumbnail_url_to_save = 'https://img.youtube.com/vi/' . $yt_video_id_extract . '/mqdefault.jpg';
            } else {
                 // If type is youtube but cannot extract ID and no custom thumbnail, keep old or set to empty
                $thumbnail_url_to_save = $video['thumbnail_url']; // Keep existing if new one cannot be formed
            }
        }


        if (empty($error_msg)) {
            $sql_update = "UPDATE videos SET
                title = '" . $title . "',
                description = '" . $description . "',
                video_url = '" . $video_url . "',
                video_type = '" . $video_type . "',
                thumbnail_url = '" . $thumbnail_url_to_save . "'
                WHERE id = " . $video_id_get;

            if (mysqli_query($conn, $sql_update)) {
                $msg = "Video details updated successfully!";
                // Re-fetch data
                $result_fetch_updated = mysqli_query($conn, $sql_fetch);
                if ($result_fetch_updated && mysqli_num_rows($result_fetch_updated) == 1) {
                    $video = mysqli_fetch_assoc($result_fetch_updated);
                }
                mysqli_free_result($result_fetch_updated);
            } else {
                $error_msg = "Error updating video: " . mysqli_error($conn);
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
                <h1 class="m-0"><?php echo $page_title; ?>: <?php echo htmlspecialchars($video['title']); ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Admin</a></li>
                    <li class="breadcrumb-item"><a href="manage_videos.php">Videos</a></li>
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
                <h3 class="card-title">Update Video Details</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $video_id_get; ?>" method="post">
                    <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">

                    <div class="form-group">
                        <label for="title">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($video['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($video['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="video_url">Video URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="video_url" name="video_url" value="<?php echo htmlspecialchars($video['video_url']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="video_type">Video Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="video_type" name="video_type" required>
                            <option value="youtube" <?php echo ($video['video_type'] == 'youtube') ? 'selected' : ''; ?>>YouTube</option>
                            <option value="vimeo" <?php echo ($video['video_type'] == 'vimeo') ? 'selected' : ''; ?>>Vimeo</option>
                            <option value="other" <?php echo ($video['video_type'] == 'other') ? 'selected' : ''; ?>>Other URL</option>
                            <!-- <option value="file" <?php // echo ($video['video_type'] == 'file') ? 'selected' : ''; ?>>Uploaded File (Not implemented)</option> -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Current Thumbnail Preview</label><br>
                        <?php
                        $display_thumbnail = $video['thumbnail_url'];
                        if ($video['video_type'] === 'youtube' && empty($display_thumbnail)) {
                            $yt_video_id_preview = '';
                            if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $video['video_url'], $matches)) {
                                $yt_video_id_preview = $matches[1];
                            } elseif (preg_match('/youtu\.be\/([^\&\?\/]+)/', $video['video_url'], $matches)) {
                                $yt_video_id_preview = $matches[1];
                            }
                            if ($yt_video_id_preview) {
                                $display_thumbnail = 'https://img.youtube.com/vi/' . $yt_video_id_preview . '/mqdefault.jpg';
                            }
                        }
                        ?>
                        <?php if (!empty($display_thumbnail)): ?>
                            <img src="<?php echo htmlspecialchars($display_thumbnail); ?>" alt="Current Thumbnail" style="max-width: 200px; max-height: 150px; border-radius: 5px; margin-bottom:10px;">
                        <?php else: ?>
                            <p>No thumbnail available or could not be auto-generated.</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="thumbnail_url">Custom Thumbnail URL (Optional)</label>
                        <input type="url" class="form-control" id="thumbnail_url" name="thumbnail_url" placeholder="Leave blank to use auto-generated for YouTube" value="<?php echo htmlspecialchars($video['thumbnail_url']); // Show stored custom one if exists ?>">
                        <small class="form-text text-muted">Enter a full URL for a custom thumbnail. If type is YouTube and this is blank, a default thumbnail will be attempted.</small>
                    </div>


                    <button type="submit" class="btn btn-primary">Update Video</button>
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
