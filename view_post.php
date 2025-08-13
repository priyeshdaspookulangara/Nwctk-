<?php
// Initialize the session
session_start();

// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$post = $comments = [];
$comment_err = $comment_text = "";
$post_id = 0;
$like_count = 0;
$user_has_liked = false;

// Process post ID from URL
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $post_id = trim($_GET["id"]);
} else {
    header("location: index.php");
    exit();
}

// Handle comment submission
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["loggedin"]) && isset($_POST['comment'])){
    if(empty(trim($_POST["comment"]))){
        $comment_err = "Comment cannot be empty.";
    } else {
        $comment_text = trim($_POST["comment"]);
    }

    if(empty($comment_err)){
        $sql = "INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "iis", $param_post_id, $param_user_id, $param_comment);
            $param_post_id = $post_id;
            $param_user_id = $_SESSION["id"];
            $param_comment = $comment_text;
            if(mysqli_stmt_execute($stmt)){
                header("location: view_post.php?id=" . $post_id);
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Fetch the post from the database
$sql_post = "SELECT p.id, p.title, p.content, p.created_at, p.user_id, u.username
             FROM posts p JOIN users u ON p.user_id = u.id
             WHERE p.id = ?";
if($stmt_post = mysqli_prepare($link, $sql_post)){
    mysqli_stmt_bind_param($stmt_post, "i", $post_id);
    if(mysqli_stmt_execute($stmt_post)){
        $result_post = mysqli_stmt_get_result($stmt_post);
        if(mysqli_num_rows($result_post) == 1){
            $post = mysqli_fetch_assoc($result_post);
        } else {
            header("location: index.php");
            exit();
        }
    }
    mysqli_stmt_close($stmt_post);
}

// Fetch comments for the post
$sql_comments = "SELECT c.comment, c.created_at, u.username
                 FROM comments c JOIN users u ON c.user_id = u.id
                 WHERE c.post_id = ? ORDER BY c.created_at ASC";
if($stmt_comments = mysqli_prepare($link, $sql_comments)){
    mysqli_stmt_bind_param($stmt_comments, "i", $post_id);
    if(mysqli_stmt_execute($stmt_comments)){
        $result_comments = mysqli_stmt_get_result($stmt_comments);
        while($row = mysqli_fetch_assoc($result_comments)){
            $comments[] = $row;
        }
    }
    mysqli_stmt_close($stmt_comments);
}

// Fetch like count
$sql_like_count = "SELECT COUNT(*) as count FROM likes WHERE post_id = ?";
if($stmt_like_count = mysqli_prepare($link, $sql_like_count)){
    mysqli_stmt_bind_param($stmt_like_count, "i", $post_id);
    if(mysqli_stmt_execute($stmt_like_count)){
        $result_like_count = mysqli_stmt_get_result($stmt_like_count);
        $row = mysqli_fetch_assoc($result_like_count);
        $like_count = $row['count'];
    }
    mysqli_stmt_close($stmt_like_count);
}

// Check if current user has liked the post
if(isset($_SESSION["loggedin"])){
    $sql_user_liked = "SELECT id FROM likes WHERE post_id = ? AND user_id = ?";
    if($stmt_user_liked = mysqli_prepare($link, $sql_user_liked)){
        mysqli_stmt_bind_param($stmt_user_liked, "ii", $post_id, $_SESSION['id']);
        if(mysqli_stmt_execute($stmt_user_liked)){
            mysqli_stmt_store_result($stmt_user_liked);
            if(mysqli_stmt_num_rows($stmt_user_liked) == 1){
                $user_has_liked = true;
            }
        }
        mysqli_stmt_close($stmt_user_liked);
    }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .wrapper { width: 800px; }
        .post-meta { color: #666; font-size: 0.9em; margin-bottom: 20px; }
        .post-content { margin-top: 20px; }
        .like-section { margin-top: 20px; padding: 10px; background-color: #f8f8f8; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        .comments-section { margin-top: 40px; }
        .comment { border-bottom: 1px solid #eee; padding: 10px 0; }
        .comment:last-child { border-bottom: none; }
        .comment-meta { font-size: 0.8em; color: #888; }
        .post-actions { margin-bottom: 20px; text-align: right; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="post-actions">
            <a href="index.php" class="btn">Back to Posts</a>
            <?php if(isset($_SESSION['loggedin']) && ($_SESSION['id'] === $post['user_id'] || $_SESSION['role'] === 'admin')): ?>
                <a href="edit_post.php?id=<?php echo $post_id; ?>" class="btn btn-secondary ml-2">Edit Post</a>
            <?php endif; ?>
        </div>
        <div class="post-content">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <p class="post-meta">
                Posted by <strong><?php echo htmlspecialchars($post['username']); ?></strong> on <?php echo date("F j, Y, g:i a", strtotime($post['created_at'])); ?>
            </p>
            <div><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
        </div>

        <div class="like-section">
            <span><strong><?php echo $like_count; ?></strong> Likes</span>
            <?php if(isset($_SESSION["loggedin"])): ?>
                <a href="like_handler.php?id=<?php echo $post_id; ?>" class="btn btn-secondary">
                    <?php echo ($user_has_liked) ? 'Unlike' : 'Like'; ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="comments-section">
            <h2>Comments</h2>
            <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $post_id; ?>" method="post">
                    <div class="form-group">
                        <textarea name="comment" class="form-control <?php echo (!empty($comment_err)) ? 'is-invalid' : ''; ?>" placeholder="Write a comment..."><?php echo $comment_text; ?></textarea>
                        <span class="invalid-feedback"><?php echo $comment_err; ?></span>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Submit Comment">
                    </div>
                </form>
            <?php else: ?>
                <p><a href="login.php">Log in</a> to post a comment.</p>
            <?php endif; ?>
            <hr>
            <?php if(empty($comments)): ?>
                <p>No comments yet.</p>
            <?php else: ?>
                <?php foreach($comments as $comment): ?>
                    <div class="comment">
                        <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                        <p class="comment-meta">
                            By <strong><?php echo htmlspecialchars($comment['username']); ?></strong> on <?php echo date("F j, Y, g:i a", strtotime($comment['created_at'])); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
