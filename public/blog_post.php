<?php
require_once '../includes/db.php';

$post_id = null;
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $post_id = $_GET['id'];
} else {
    // Redirect to blog index if ID is not valid or not present
    header("Location: blog.php");
    exit;
}

$is_preview = isset($_GET['preview']) && $_GET['preview'] === 'true';
session_start(); // To check for admin role for preview
$is_admin = isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true;


$post = null;
$sql = "SELECT bp.id, bp.title, bp.content, bp.image_url, u.username as author_name, bp.created_at, bc.name as category_name, bc.id as category_id
        FROM blog_posts bp
        LEFT JOIN blog_categories bc ON bp.category_id = bc.id
        JOIN users u ON bp.author_id = u.id
        WHERE bp.id = ?";

if (!$is_preview || !$is_admin) {
    $sql .= " AND bp.status = 'published'";
}

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) == 1) {
        $post = mysqli_fetch_assoc($result);
        if (strpos($post['image_url'], 'uploads/') !== false) {
            // Path is already good
        } elseif (strpos($post['image_url'], '../public/') === 0) {
            $post['image_url'] = substr($post['image_url'], strlen('../public/'));
        }
    } else {
        header("Location: blog.php?error=notfound");
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    die("Error preparing statement: " . mysqli_error($conn));
}
// Re-open connection for likes/comments
require_once '../includes/db.php';

// Fetch Like Count
$sql_likes = "SELECT COUNT(*) as like_count FROM post_likes WHERE post_id = ?";
$stmt_likes = mysqli_prepare($conn, $sql_likes);
mysqli_stmt_bind_param($stmt_likes, "i", $post_id);
mysqli_stmt_execute($stmt_likes);
$like_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_likes))['like_count'];
mysqli_stmt_close($stmt_likes);

// Check if current user has liked this post
$user_has_liked = false;
if (isset($_SESSION['user_loggedin']) && $_SESSION['user_loggedin'] === true) {
    $user_id = $_SESSION['user_id'];
    $sql_check_like = "SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?";
    $stmt_check_like = mysqli_prepare($conn, $sql_check_like);
    mysqli_stmt_bind_param($stmt_check_like, "ii", $post_id, $user_id);
    mysqli_stmt_execute($stmt_check_like);
    mysqli_stmt_store_result($stmt_check_like);
    if (mysqli_stmt_num_rows($stmt_check_like) > 0) {
        $user_has_liked = true;
    }
    mysqli_stmt_close($stmt_check_like);
}

// Re-open connection for likes/comments
require_once '../includes/db.php';

// Handle Comment Submission
$comment_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_comment'])) {
    if (!isset($_SESSION['user_loggedin']) || $_SESSION['user_loggedin'] !== true) {
        header("location: login.php"); // Should not happen if form is hidden, but a safeguard
        exit;
    }
    $comment_text = trim($_POST['comment']);
    if (empty($comment_text)) {
        $comment_error = "Comment cannot be empty.";
    } else {
        $user_id = $_SESSION['user_id'];
        $sql_insert_comment = "INSERT INTO post_comments (post_id, user_id, comment) VALUES (?, ?, ?)";
        if ($stmt_insert_comment = mysqli_prepare($conn, $sql_insert_comment)) {
            mysqli_stmt_bind_param($stmt_insert_comment, "iis", $post_id, $user_id, $comment_text);
            mysqli_stmt_execute($stmt_insert_comment);
            header("location: blog_post.php?id=" . $post_id); // Refresh to show new comment
            exit;
        }
    }
}


// Fetch Like Count & User Like Status (as before)
// ... (code for likes is here)

// Fetch Comments
$comments = [];
$sql_comments = "SELECT c.comment, c.created_at, u.username FROM post_comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at DESC";
$stmt_comments = mysqli_prepare($conn, $sql_comments);
mysqli_stmt_bind_param($stmt_comments, "i", $post_id);
mysqli_stmt_execute($stmt_comments);
$result_comments = mysqli_stmt_get_result($stmt_comments);
while ($row = mysqli_fetch_assoc($result_comments)) {
    $comments[] = $row;
}
mysqli_stmt_close($stmt_comments);


mysqli_close($conn);

$page_title = htmlspecialchars($post['title']);
require_once 'includes/header.php';
?>

<div class="page-header"><!-- ... as before ... --></div>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="blog-post-content">
                <!-- ... post content and like button as before ... -->
                <hr class="my-4">

                <!-- Comments Section -->
                <div id="comments-section" class="mt-5">
                    <h3>Comments (<?php echo count($comments); ?>)</h3>
                    <hr>

                    <!-- Comment Form -->
                    <?php if (isset($_SESSION['user_loggedin']) && $_SESSION['user_loggedin'] === true): ?>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Leave a Comment</h5>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $post_id; ?>" method="post">
                                    <div class="form-group">
                                        <textarea class="form-control" name="comment" rows="3" required></textarea>
                                        <?php if($comment_error) echo "<small class='form-text text-danger'>$comment_error</small>"; ?>
                                    </div>
                                    <button type="submit" name="submit_comment" class="btn btn-primary">Submit</button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <p><a href="login.php">Log in</a> to leave a comment.</p>
                    <?php endif; ?>

                    <!-- Display Comments -->
                    <?php if (empty($comments)): ?>
                        <p>No comments yet. Be the first to comment!</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="media mb-3">
                                <i class="fas fa-user-circle fa-2x mr-3"></i>
                                <div class="media-body">
                                    <h6 class="mt-0 mb-1"><?php echo htmlspecialchars($comment['username']); ?>
                                        <small class="text-muted">- <?php echo date("M d, Y", strtotime($comment['created_at'])); ?></small>
                                    </h6>
                                    <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<!-- ... like button script as before ... -->
<style>
/* ... styles as before ... */
#comments-section .media {
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
}
</style>
