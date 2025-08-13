<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Edit Blog Post";
$msg = "";
$error_msg = "";
$post_id = null;
$post = null;

// Get post ID from URL
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $post_id = $_GET['id'];
} else {
    header("Location: manage_blog_posts.php?error=Invalid Post ID");
    exit;
}

// Fetch existing post data
$sql_fetch_post = "SELECT id, title, content, category_id, author, image_url FROM blog_posts WHERE id = ?";
if ($stmt_fetch = mysqli_prepare($conn, $sql_fetch_post)) {
    mysqli_stmt_bind_param($stmt_fetch, "i", $post_id);
    mysqli_stmt_execute($stmt_fetch);
    $result_fetch_post = mysqli_stmt_get_result($stmt_fetch);
    if ($result_fetch_post && mysqli_num_rows($result_fetch_post) == 1) {
        $post = mysqli_fetch_assoc($result_fetch_post);
    } else {
        header("Location: manage_blog_posts.php?error=Post not found");
        exit;
    }
    mysqli_stmt_close($stmt_fetch);
} else {
    die("Error preparing fetch statement: " . mysqli_error($conn));
}

// Fetch categories for the dropdown
$categories = [];
$sql_categories = "SELECT id, name FROM blog_categories ORDER BY name ASC";
$result_categories = mysqli_query($conn, $sql_categories);
if ($result_categories) {
    while ($row = mysqli_fetch_assoc($result_categories)) {
        $categories[] = $row;
    }
    mysqli_free_result($result_categories);
}

// Define upload directory
$upload_dir_server = "../public/uploads/blog_posts/";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $posted_post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
    if ($posted_post_id !== $post_id) {
        $error_msg = "Error: Post ID mismatch.";
    } else {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $author = trim($_POST['author']);

        $current_image_url = $post['image_url'];

        // Image Upload Handling
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $file_name = basename($_FILES["image"]["name"]);
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if (!is_dir($upload_dir_server)) mkdir($upload_dir_server, 0777, true);
            $target_file_server = $upload_dir_server . uniqid() . '_' . $file_name;
            $target_file_web = str_replace('../public/', '../public/', $target_file_server);


            if (in_array($file_type, $allowed_types) && $_FILES["image"]["size"] < 5000000) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file_server)) {
                    if (!empty($current_image_url)) {
                        $old_image_server_path = realpath(dirname(__FILE__) . '/' . $current_image_url);
                        if ($old_image_server_path && file_exists($old_image_server_path)) {
                            unlink($old_image_server_path);
                        }
                    }
                    $current_image_url = $target_file_web;
                } else {
                    $error_msg .= "Sorry, there was an error uploading your new file. ";
                }
            } else {
                $error_msg .= "Invalid file type or size (Max 5MB: JPG, JPEG, PNG, GIF). ";
            }
        }

        if (empty($title)) $error_msg .= "Title is required. ";
        if (empty($content)) $error_msg .= "Content is required. ";
        if (empty($category_id)) $error_msg .= "Category is required. ";

        if (empty($error_msg)) {
            $sql_update = "UPDATE blog_posts SET title = ?, content = ?, category_id = ?, author = ?, image_url = ? WHERE id = ?";

            if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
                mysqli_stmt_bind_param($stmt_update, "ssissi", $title, $content, $category_id, $author, $current_image_url, $post_id);

                if (mysqli_stmt_execute($stmt_update)) {
                    $msg = "Blog post updated successfully!";
                    // Update post array to show new data in the form
                    $post['title'] = $title;
                    $post['content'] = $content;
                    $post['category_id'] = $category_id;
                    $post['author'] = $author;
                    $post['image_url'] = $current_image_url;
                } else {
                    $error_msg = "Error updating post: " . mysqli_stmt_error($stmt_update);
                }
                mysqli_stmt_close($stmt_update);
            } else {
                $error_msg = "Error preparing update statement: " . mysqli_error($conn);
            }
        }
    }
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
                    <li class="breadcrumb-item"><a href="manage_blog_posts.php">Blog Posts</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Update Post Details</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $post_id; ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">

                    <div class="form-group">
                        <label for="title">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Category <span class="text-danger">*</span></label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Select a Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($post['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="content">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="author">Author</label>
                        <input type="text" class="form-control" id="author" name="author" value="<?php echo htmlspecialchars($post['author']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="image">New Featured Image (Optional)</label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                        <?php if (!empty($post['image_url'])): ?>
                            <p class="mt-2"><small>Current Image:</small><br>
                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="max-width: 150px; border-radius: 5px;">
                            </p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Post</button>
                    <a href="manage_blog_posts.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
