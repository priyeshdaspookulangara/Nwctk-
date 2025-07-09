<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Manage Videos";
$msg = "";
$error_msg = "";

// Handle Delete Action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        // For linked videos, no file deletion is typically needed unless they were 'file' type
        // and we had implemented file uploads for videos.
        // $sql_select_video = "SELECT video_url, video_type, thumbnail_url FROM videos WHERE id = " . $delete_id;
        // $result_video = mysqli_query($conn, $sql_select_video);
        // if ($result_video && mysqli_num_rows($result_video) > 0) {
        //     $row_video = mysqli_fetch_assoc($result_video);
        //     if ($row_video['video_type'] === 'file') {
        //         // Logic to delete video file and thumbnail if they were uploaded
        //     }
        // }
        // mysqli_free_result($result_video);

        $sql_delete = "DELETE FROM videos WHERE id = " . $delete_id;
        if (mysqli_query($conn, $sql_delete)) {
            $msg = "Video deleted successfully.";
        } else {
            $error_msg = "Error deleting video: " . mysqli_error($conn);
        }
    } else {
        $error_msg = "Invalid ID for deletion.";
    }
}

// Fetch all videos
$videos = [];
$sql = "SELECT id, title, video_url, video_type, thumbnail_url, uploaded_at FROM videos ORDER BY uploaded_at DESC";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Auto-generate YouTube thumbnail if missing and type is YouTube
        if ($row['video_type'] === 'youtube' && empty($row['thumbnail_url'])) {
            $video_id = '';
            if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $row['video_url'], $matches)) {
                $video_id = $matches[1];
            } elseif (preg_match('/youtu\.be\/([^\&\?\/]+)/', $row['video_url'], $matches)) {
                $video_id = $matches[1];
            }
            if ($video_id) {
                $row['thumbnail_url'] = 'https://img.youtube.com/vi/' . $video_id . '/mqdefault.jpg';
            }
        }
        $videos[] = $row;
    }
    mysqli_free_result($result);
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
                    <li class="breadcrumb-item active">Videos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">List of Videos</h3>
                <div class="card-tools">
                    <a href="add_video.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Video
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <?php if (empty($videos)): ?>
                    <div class="alert alert-info">No videos found. <a href="add_video.php">Add one now</a>.</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($videos as $video): ?>
                            <div class="col-md-4 col-sm-6 mb-4">
                                <div class="card h-100">
                                    <?php if (!empty($video['thumbnail_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($video['video_url']); ?>" target="_blank" title="Watch Video">
                                            <img src="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($video['title']); ?>" style="height: 180px; object-fit: cover;">
                                        </a>
                                    <?php else: ?>
                                        <div class="card-img-top d-flex align-items-center justify-content-center" style="height: 180px; background-color: #f0f0f0;">
                                            <span class="text-muted">No Thumbnail</span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a href="<?php echo htmlspecialchars($video['video_url']); ?>" target="_blank">
                                                <?php echo htmlspecialchars($video['title']); ?>
                                            </a>
                                        </h5>
                                        <p class="card-text"><small class="text-muted">Type: <?php echo ucfirst(htmlspecialchars($video['video_type'])); ?></small></p>
                                        <p class="card-text"><small class="text-muted">Uploaded: <?php echo date("d M, Y", strtotime($video['uploaded_at'])); ?></small></p>
                                    </div>
                                    <div class="card-footer text-center">
                                        <a href="edit_video.php?id=<?php echo $video['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this video entry?');">
                                            <input type="hidden" name="delete_id" value="<?php echo $video['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
