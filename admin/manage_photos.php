<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Manage Photos";
$msg = "";
$error_msg = "";

// Handle Delete Action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        // Get image_url to delete the file
        $sql_select_image = "SELECT image_url FROM photos WHERE id = " . $delete_id;
        $result_image = mysqli_query($conn, $sql_select_image);
        if ($result_image && mysqli_num_rows($result_image) > 0) {
            $row_image = mysqli_fetch_assoc($result_image);
            $image_to_delete_web = $row_image['image_url'];

            if (!empty($image_to_delete_web)) {
                // Path is relative to public, this script is in admin.
                // e.g. ../public/uploads/photos/image.jpg
                $image_to_delete_server = realpath(dirname(__FILE__) . '/' . $image_to_delete_web);
                if ($image_to_delete_server && file_exists($image_to_delete_server)) {
                    unlink($image_to_delete_server);
                }
            }
        }
        mysqli_free_result($result_image);

        // Delete the record
        $sql_delete = "DELETE FROM photos WHERE id = " . $delete_id;
        if (mysqli_query($conn, $sql_delete)) {
            $msg = "Photo deleted successfully.";
        } else {
            $error_msg = "Error deleting photo: " . mysqli_error($conn);
        }
    } else {
        $error_msg = "Invalid ID for deletion.";
    }
}

// Fetch all photos
$photos = [];
$sql = "SELECT id, title, description, image_url, gallery_tag, uploaded_at FROM photos ORDER BY uploaded_at DESC";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $photos[] = $row;
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
                    <li class="breadcrumb-item active">Photos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">List of Photos</h3>
                <div class="card-tools">
                    <a href="add_photo.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Photo
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

                <?php if (empty($photos)): ?>
                    <div class="alert alert-info">No photos found. <a href="add_photo.php">Add one now</a>.</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($photos as $photo): ?>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="card h-100">
                                    <img src="<?php echo htmlspecialchars($photo['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($photo['title']); ?>" style="height: 200px; object-fit: cover;">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($photo['title']); ?></h5>
                                        <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($photo['description'] ? substr($photo['description'], 0, 50) . '...' : 'No description'); ?></small></p>
                                        <p class="card-text"><small class="text-muted">Tag: <?php echo htmlspecialchars($photo['gallery_tag'] ? $photo['gallery_tag'] : 'N/A'); ?></small></p>
                                        <p class="card-text"><small class="text-muted">Uploaded: <?php echo date("d M, Y", strtotime($photo['uploaded_at'])); ?></small></p>
                                    </div>
                                    <div class="card-footer text-center">
                                        <a href="edit_photo.php?id=<?php echo $photo['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this photo? This action cannot be undone.');">
                                            <input type="hidden" name="delete_id" value="<?php echo $photo['id']; ?>">
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
