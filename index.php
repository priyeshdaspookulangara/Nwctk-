<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";

// Define array to hold posts
$posts = [];

// Attempt to select all posts from the database with like counts
$sql = "SELECT p.id, p.title, p.content, p.created_at, u.username, COUNT(l.id) as like_count
        FROM posts p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN likes l ON p.id = l.post_id
        WHERE p.status = 'published'
        GROUP BY p.id, p.title, p.content, p.created_at, u.username
        ORDER BY p.created_at DESC";

if($result = mysqli_query($link, $sql)){
    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            $posts[] = $row;
        }
        // Free result set
        mysqli_free_result($result);
    }
} else{
    echo "Oops! Something went wrong. Please try again later.";
}

// Close connection
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .post-container {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .post-meta {
            color: #666;
            font-size: 0.9em;
        }
        .post-footer {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9em;
        }
        .wrapper {
            width: 800px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h1 class="my-5">Hi, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. Welcome to the blog.</h1>
        <p style="text-align: left;">
            <?php if(in_array($_SESSION["role"], ["member", "intern", "volunteer"])): ?>
                <a href="create_post.php" class="btn btn-primary">Create New Post</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-danger ml-3">Sign Out</a>
        </p>

        <h2>Recent Posts</h2>

        <?php if(empty($posts)): ?>
            <p>No posts have been created yet.</p>
        <?php else: ?>
            <?php foreach($posts as $post): ?>
                <div class="post-container">
                    <h3><a href="view_post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                    <p class="post-meta">
                        Posted by <strong><?php echo htmlspecialchars($post['username']); ?></strong> on <?php echo date("F j, Y, g:i a", strtotime($post['created_at'])); ?>
                    </p>
                    <p><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>...</p>
                    <div class="post-footer">
                        <span><strong><?php echo $post['like_count']; ?></strong> Likes</span>
                        <a href="view_post.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary">Read More & Comment</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
