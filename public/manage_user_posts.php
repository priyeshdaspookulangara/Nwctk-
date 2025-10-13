<?php
session_start();
require_once '../includes/db.php';

// If the user is not logged in, or not a contributor, redirect them.
$contributor_roles = ['member', 'volunteer', 'intern'];
if (!isset($_SESSION["user_loggedin"]) || !in_array($_SESSION['user_role'], $contributor_roles)) {
    header("location: login.php");
    exit;
}

$page_title = "My Blog Posts";
$user_id = $_SESSION['user_id'];
$msg = "";
$error_msg = "";

// Handle Delete Action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        // First, verify this post belongs to the current user
        $sql_verify = "SELECT image_url FROM blog_posts WHERE id = ? AND author_id = ?";
        if ($stmt_verify = mysqli_prepare($conn, $sql_verify)) {
            mysqli_stmt_bind_param($stmt_verify, "ii", $delete_id, $user_id);
            mysqli_stmt_execute($stmt_verify);
            $result_verify = mysqli_stmt_get_result($stmt_verify);

            if ($row_image = mysqli_fetch_assoc($result_verify)) {
                // User is authorized to delete, proceed with deleting image and post
                if (!empty($row_image['image_url']) && file_exists($row_image['image_url'])) {
                    unlink($row_image['image_url']);
                }

                $sql_delete = "DELETE FROM blog_posts WHERE id = ?";
                if ($stmt_delete = mysqli_prepare($conn, $sql_delete)) {
                    mysqli_stmt_bind_param($stmt_delete, "i", $delete_id);
                    mysqli_stmt_execute($stmt_delete);
                    $msg = "Post deleted successfully.";
                }
            } else {
                $error_msg = "You are not authorized to delete this post.";
            }
        }
    }
}


// Fetch all posts by the current user
$posts = [];
$sql = "SELECT bp.id, bp.title, bp.status, bp.created_at, bc.name as category_name
        FROM blog_posts bp
        LEFT JOIN blog_categories bc ON bp.category_id = bc.id
        WHERE bp.author_id = ?
        ORDER BY bp.created_at DESC";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $posts[] = $row;
        }
    }
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <p class="lead">Manage your submitted blog posts.</p>
    </div>
</div>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Your Submissions</h3>
            <div class="card-tools">
                <a href="add_user_post.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Post</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>
            <?php if (!empty($error_msg)) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>

            <?php if (empty($posts)): ?>
                <p>You have not submitted any posts yet. <a href="add_user_post.php">Write your first one!</a></p>
            <?php else: ?>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Submitted On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td><?php echo htmlspecialchars($post['category_name'] ?? 'N/A'); ?></td>
                                <td><?php echo date("d M, Y", strtotime($post['created_at'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php
                                        switch($post['status']) {
                                            case 'published': echo 'success'; break;
                                            case 'pending_approval': echo 'warning'; break;
                                            case 'rejected': echo 'danger'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>"><?php echo ucfirst(str_replace('_', ' ', $post['status'])); ?></span>
                                </td>
                                <td>
                                    <a href="edit_user_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-info" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $post['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
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

<?php
mysqli_close($conn);
require_once 'includes/footer.php';
?>
