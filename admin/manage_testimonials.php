<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Manage Testimonials";
$msg = "";
$error_msg = "";

// Handle Delete Action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        // Get image_url to delete the file if it exists
        $sql_select_image = "SELECT image_url FROM testimonials WHERE id = " . $delete_id;
        $result_image = mysqli_query($conn, $sql_select_image);
        if ($result_image && mysqli_num_rows($result_image) > 0) {
            $row_image = mysqli_fetch_assoc($result_image);
            $image_to_delete_web = $row_image['image_url'];

            if (!empty($image_to_delete_web)) {
                $image_to_delete_server = realpath(dirname(__FILE__) . '/' . $image_to_delete_web);
                if ($image_to_delete_server && file_exists($image_to_delete_server)) {
                    unlink($image_to_delete_server);
                }
            }
        }
        mysqli_free_result($result_image);

        // Delete the record
        $sql_delete = "DELETE FROM testimonials WHERE id = " . $delete_id;
        if (mysqli_query($conn, $sql_delete)) {
            $msg = "Testimonial deleted successfully.";
        } else {
            $error_msg = "Error deleting testimonial: " . mysqli_error($conn);
        }
    } else {
        $error_msg = "Invalid ID for deletion.";
    }
}

// Fetch all testimonials
$testimonials = [];
$sql = "SELECT id, author_name, author_position, testimonial_text, image_url, rating, created_at FROM testimonials ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $testimonials[] = $row;
    }
    mysqli_free_result($result);
}

function display_stars($rating) {
    $stars_html = '';
    if ($rating === null || $rating == 0) return 'N/A';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars_html .= '<i class="fas fa-star text-warning"></i>'; // Full star
        } else {
            $stars_html .= '<i class="far fa-star text-warning"></i>'; // Empty star
        }
    }
    return $stars_html;
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
                    <li class="breadcrumb-item active">Testimonials</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">List of Testimonials</h3>
                <div class="card-tools">
                    <a href="add_testimonial.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Testimonial
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

                <?php if (empty($testimonials)): ?>
                    <div class="alert alert-info">No testimonials found. <a href="add_testimonial.php">Add one now</a>.</div>
                <?php else: ?>
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Author Image</th>
                                <th>Author Name</th>
                                <th>Position</th>
                                <th>Testimonial (Excerpt)</th>
                                <th>Rating</th>
                                <th>Added On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count = 1; foreach ($testimonials as $testimonial): ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td>
                                    <?php if (!empty($testimonial['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($testimonial['image_url']); ?>" alt="<?php echo htmlspecialchars($testimonial['author_name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($testimonial['author_name']); ?></td>
                                <td><?php echo htmlspecialchars($testimonial['author_position'] ? $testimonial['author_position'] : 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(substr($testimonial['testimonial_text'], 0, 100)); ?>...</td>
                                <td><?php echo display_stars($testimonial['rating']); ?></td>
                                <td><?php echo date("d M, Y", strtotime($testimonial['created_at'])); ?></td>
                                <td>
                                    <a href="edit_testimonial.php?id=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this testimonial? This action cannot be undone.');">
                                        <input type="hidden" name="delete_id" value="<?php echo $testimonial['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
