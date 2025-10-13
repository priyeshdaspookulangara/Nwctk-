<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_loggedin']) || $_SESSION['user_loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to like a post.']);
    exit;
}

if (!isset($_POST['post_id']) || !filter_var($_POST['post_id'], FILTER_VALIDATE_INT)) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

// Check if the user has already liked the post
$sql_check = "SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ii", $post_id, $user_id);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_store_result($stmt_check);

$action = '';

if (mysqli_stmt_num_rows($stmt_check) > 0) {
    // User has liked, so unlike
    $sql_delete = "DELETE FROM post_likes WHERE post_id = ? AND user_id = ?";
    $stmt_delete = mysqli_prepare($conn, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "ii", $post_id, $user_id);
    mysqli_stmt_execute($stmt_delete);
    $action = 'unliked';
} else {
    // User has not liked, so like
    $sql_insert = "INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)";
    $stmt_insert = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, "ii", $post_id, $user_id);
    mysqli_stmt_execute($stmt_insert);
    $action = 'liked';
}

// Get the new like count
$sql_count = "SELECT COUNT(*) as like_count FROM post_likes WHERE post_id = ?";
$stmt_count = mysqli_prepare($conn, $sql_count);
mysqli_stmt_bind_param($stmt_count, "i", $post_id);
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$like_count = mysqli_fetch_assoc($result_count)['like_count'];

echo json_encode(['success' => true, 'action' => $action, 'like_count' => $like_count]);

mysqli_close($conn);
exit;
?>
