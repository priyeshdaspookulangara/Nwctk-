<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Manage Pending Posts";
$msg = "";
$error_msg = "";

// Handle Approve/Reject Action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_id']) && isset($_POST['action'])) {
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
    $action = $_POST['action']; // 'approve' or 'reject'

    if ($post_id && in_array($action, ['approve', 'reject'])) {
        $new_status = ($action === 'approve') ? 'published' : 'rejected';

        $sql = "UPDATE blog_posts SET status = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $new_status, $post_id);
            if (mysqli_stmt_execute($stmt)) {
                $msg = "Post status updated to '" . ucfirst($new_status) . "'.";
            } else {
                $error_msg = "Error updating post status.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error_msg = "Invalid action or post ID.";
    }
}

// Fetch all posts pending approval
$posts = [];
$sql_fetch = "SELECT bp.id, bp.title, bp.created_at, u.username as author_name, bc.name as category_name
              FROM blog_posts bp
              JOIN users u ON bp.author_id = u.id
              LEFT JOIN blog_categories bc ON bp.category_id = bc.id
              WHERE bp.status = 'pending_approval'
              ORDER BY bp.created_at ASC";
$result = mysqli_query($conn, $sql_fetch);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><?php echo $page_title; ?></h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Admin</a></li>
                    <li class="breadcrumb-item active">Pending Posts</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Posts Awaiting Review</h3></div>
            <div class="card-body">
                <?php if ($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
                <?php if ($error_msg) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>

                <?php if (empty($posts)): ?>
                    <div class="alert alert-info">No posts are currently pending approval.</div>
                <?php else: ?>
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Submitted On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                <td><?php echo htmlspecialchars($post['category_name'] ?? 'N/A'); ?></td>
                                <td><?php echo date("d M, Y H:i", strtotime($post['created_at'])); ?></td>
                                <td>
                                    <a href="../public/blog_post.php?id=<?php echo $post['id']; ?>&preview=true" class="btn btn-sm btn-secondary" target="_blank" title="Preview"><i class="fas fa-eye"></i></a>
                                    <form action="" method="post" style="display:inline;" onsubmit="return confirm('Approve this post?');">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" title="Approve"><i class="fas fa-check"></i></button>
                                    </form>
                                    <form action="" method="post" style="display:inline;" onsubmit="return confirm('Reject this post?');">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" title="Reject"><i class="fas fa-times"></i></button>
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
