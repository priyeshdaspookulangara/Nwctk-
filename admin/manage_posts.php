<?php
// Initialize the session
session_start();

// Include config file
require_once "../config.php";

// Admin-only access
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../login.php");
    exit;
}

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Handle approve/reject actions
if(isset($_GET['action']) && isset($_GET['id'])){
    $action = $_GET['action'];
    $post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $new_status = "";

    if($action === 'approve'){
        $new_status = 'published';
    } elseif ($action === 'reject'){
        $new_status = 'draft';
    }

    if(!empty($new_status) && $post_id){
        $sql_update = "UPDATE posts SET status = ? WHERE id = ?";
        if($stmt_update = mysqli_prepare($link, $sql_update)){
            mysqli_stmt_bind_param($stmt_update, "si", $new_status, $post_id);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
            header("location: manage_posts.php");
            exit;
        }
    }
}

// Fetch pending posts
$pending_posts = [];
$sql_fetch = "SELECT p.id, p.title, p.created_at, u.username
              FROM posts p
              JOIN users u ON p.user_id = u.id
              WHERE p.status = 'pending_approval'
              ORDER BY p.created_at ASC";

if($result = mysqli_query($link, $sql_fetch)){
    while($row = mysqli_fetch_assoc($result)){
        $pending_posts[] = $row;
    }
    mysqli_free_result($result);
}
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Posts</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .wrapper { width: 90%; max-width: 1000px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="page-header">
            <h1>Manage Pending Posts</h1>
            <a href="index.php" class="btn">Back to User Management</a>
        </div>

        <?php if(empty($pending_posts)): ?>
            <p>No posts are currently pending approval.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Submitted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pending_posts as $post): ?>
                        <tr>
                            <td><a href="../view_post.php?id=<?php echo $post['id']; ?>" target="_blank"><?php echo htmlspecialchars($post['title']); ?></a></td>
                            <td><?php echo htmlspecialchars($post['username']); ?></td>
                            <td><?php echo date("F j, Y", strtotime($post['created_at'])); ?></td>
                            <td>
                                <a href="manage_posts.php?action=approve&id=<?php echo $post['id']; ?>" class="btn btn-primary">Approve</a>
                                <a href="manage_posts.php?action=reject&id=<?php echo $post['id']; ?>" class="btn btn-danger ml-2">Reject</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
