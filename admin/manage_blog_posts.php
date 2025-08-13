<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Manage Blog Posts";
$msg = "";
$error_msg = "";

// Handle Delete Action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        // Get image_url to delete the file
        $sql_select_image = "SELECT image_url FROM blog_posts WHERE id = ?";
        if ($stmt_select = mysqli_prepare($conn, $sql_select_image)) {
            mysqli_stmt_bind_param($stmt_select, "i", $delete_id);
            mysqli_stmt_execute($stmt_select);
            $result_image = mysqli_stmt_get_result($stmt_select);
            if ($result_image && $row_image = mysqli_fetch_assoc($result_image)) {
                $image_to_delete_web = $row_image['image_url'];
                if (!empty($image_to_delete_web)) {
                    $image_to_delete_server = realpath(dirname(__FILE__) . '/' . $image_to_delete_web);
                    if ($image_to_delete_server && file_exists($image_to_delete_server)) {
                        unlink($image_to_delete_server);
                    }
                }
            }
            mysqli_stmt_close($stmt_select);
        }

        // Delete the record
        $sql_delete = "DELETE FROM blog_posts WHERE id = ?";
        if ($stmt_delete = mysqli_prepare($conn, $sql_delete)) {
            mysqli_stmt_bind_param($stmt_delete, "i", $delete_id);
            if (mysqli_stmt_execute($stmt_delete)) {
                $msg = "Blog post deleted successfully.";
            } else {
                $error_msg = "Error deleting post: " . mysqli_stmt_error($stmt_delete);
            }
            mysqli_stmt_close($stmt_delete);
        } else {
            $error_msg = "Error preparing delete statement: " . mysqli_error($conn);
        }
    } else {
        $error_msg = "Invalid ID for deletion.";
    }
}

// Fetch all blog posts with category names
$posts = [];
$sql = "SELECT bp.id, bp.title, bp.image_url, bp.author, bp.created_at, bc.name as category_name
        FROM blog_posts bp
        LEFT JOIN blog_categories bc ON bp.category_id = bc.id
        ORDER BY bp.created_at DESC";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
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
                    <li class="breadcrumb-item active">Blog Posts</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">List of Blog Posts</h3>
                <div class="card-tools">
                    <a href="add_blog_post.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Post
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

                <?php if (empty($posts)): ?>
                    <div class="alert alert-info">No blog posts found. <a href="add_blog_post.php">Add one now</a>.</div>
                <?php else: ?>
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Author</th>
                                <th>Published On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count = 1; foreach ($posts as $post): ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td>
                                    <?php if (!empty($post['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="width: 70px; height: auto; border-radius: 3px;">
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></td>
                                <td><?php echo htmlspecialchars($post['author']); ?></td>
                                <td><?php echo date("d M, Y", strtotime($post['created_at'])); ?></td>
                                <td>
                                    <a href="edit_blog_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">
                                        <input type="hidden" name="delete_id" value="<?php echo $post['id']; ?>">
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
