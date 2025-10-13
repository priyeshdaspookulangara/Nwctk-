<?php
session_start();
require_once '../includes/db.php';

// Authentication and Authorization
$contributor_roles = ['member', 'volunteer', 'intern'];
if (!isset($_SESSION["user_loggedin"]) || !in_array($_SESSION['user_role'], $contributor_roles)) {
    header("location: login.php");
    exit;
}

$page_title = "Edit Blog Post";
$user_id = $_SESSION['user_id'];
$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$post_id) {
    header("location: manage_user_posts.php");
    exit;
}

// Fetch the post, ensuring it belongs to the current user
$sql_fetch = "SELECT * FROM blog_posts WHERE id = ? AND author_id = ?";
if ($stmt_fetch = mysqli_prepare($conn, $sql_fetch)) {
    mysqli_stmt_bind_param($stmt_fetch, "ii", $post_id, $user_id);
    mysqli_stmt_execute($stmt_fetch);
    $result = mysqli_stmt_get_result($stmt_fetch);
    if ($post = mysqli_fetch_assoc($result)) {
        // Post belongs to user
    } else {
        // Post not found or doesn't belong to user
        header("location: manage_user_posts.php?error=unauthorized");
        exit;
    }
}

// Fetch categories
$categories = [];
$result_categories = mysqli_query($conn, "SELECT id, name FROM blog_categories ORDER BY name ASC");
if ($result_categories) {
    while ($row = mysqli_fetch_assoc($result_categories)) $categories[] = $row;
}

$msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $status = 'pending_approval'; // Editing requires re-approval
    $current_image_url = $post['image_url'];

    // Handle image upload if a new one is provided
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $upload_dir = "uploads/blog_posts/";
        if (!empty($current_image_url) && file_exists($current_image_url)) {
            unlink($current_image_url);
        }
        $current_image_url = $upload_dir . uniqid() . '_' . basename($_FILES["image"]["name"]);
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $current_image_url)) {
            $error_msg .= "Error uploading new image. ";
            $current_image_url = $post['image_url']; // Revert to old image
        }
    }

    if (empty($title) || empty($content) || empty($category_id)) {
        $error_msg .= "All fields are required. ";
    }

    if (empty($error_msg)) {
        $sql_update = "UPDATE blog_posts SET title = ?, content = ?, category_id = ?, image_url = ?, status = ? WHERE id = ? AND author_id = ?";
        if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
            mysqli_stmt_bind_param($stmt_update, "ssisssi", $title, $content, $category_id, $current_image_url, $status, $post_id, $user_id);
            if (mysqli_stmt_execute($stmt_update)) {
                $msg = "Post updated and submitted for re-approval.";
                // Re-fetch post to show updated data
                $result = mysqli_query($conn, "SELECT * FROM blog_posts WHERE id = $post_id");
                $post = mysqli_fetch_assoc($result);
            } else {
                $error_msg = "Error updating post.";
            }
        }
    }
}

require_once 'includes/header.php';
?>
<div class="page-header"><div class="container"><h1><?php echo $page_title; ?></h1></div></div>
<div class="container mt-4">
    <div class="card">
        <div class="card-header">Editing: <?php echo htmlspecialchars($post['title']); ?></div>
        <div class="card-body">
            <?php if ($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
            <?php if ($error_msg) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($post['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea name="content" id="content" class="form-control" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Change Featured Image</label>
                    <input type="file" name="image" id="image" class="form-control-file">
                    <?php if(!empty($post['image_url'])): ?>
                        <p class="mt-2"><small>Current:</small><br><img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Current Image" style="max-width: 200px;"></p>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Save and Submit for Review</button>
                <a href="manage_user_posts.php" class="btn btn-secondary">Back to My Posts</a>
            </form>
        </div>
    </div>
</div>
<?php
mysqli_close($conn);
require_once 'includes/footer.php';
?>
