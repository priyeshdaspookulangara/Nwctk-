<?php
session_start();
require_once '../includes/db.php';

// If the user is not logged in, or not a contributor, redirect them.
$contributor_roles = ['member', 'volunteer', 'intern'];
if (!isset($_SESSION["user_loggedin"]) || !in_array($_SESSION['user_role'], $contributor_roles)) {
    header("location: login.php");
    exit;
}

$page_title = "Add New Blog Post";
$msg = "";
$error_msg = "";

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
$upload_dir_server = "uploads/blog_posts/"; // Relative to public folder
if (!is_dir($upload_dir_server)) {
    mkdir($upload_dir_server, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $author_id = $_SESSION['user_id'];
    $status = 'pending_approval'; // Posts from users need approval

    $image_url = "";

    // Image Upload Handling
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        // Validation logic here as before
        $image_url = "uploads/blog_posts/" . uniqid() . '_' . basename($_FILES["image"]["name"]);
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $image_url)) {
            $error_msg .= "Sorry, there was an error uploading your file. ";
            $image_url = "";
        }
    }

    if (empty($title)) $error_msg .= "Title is required. ";
    if (empty($content)) $error_msg .= "Content is required. ";
    if (empty($category_id)) $error_msg .= "Category is required. ";

    if (empty($error_msg)) {
        $sql = "INSERT INTO blog_posts (title, content, category_id, author_id, image_url, status) VALUES (?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssiiss", $title, $content, $category_id, $author_id, $image_url, $status);

            if (mysqli_stmt_execute($stmt)) {
                $msg = "Your blog post has been submitted for review!";
                $_POST = array(); // Clear form
            } else {
                $error_msg = "Error: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = "Error preparing statement: " . mysqli_error($conn);
        }
    }
}
require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <p class="lead">Share your story or update with the community.</p>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    New Post Details
                </div>
                <div class="card-body">
                    <?php if (!empty($msg)): ?>
                        <div class="alert alert-success"><?php echo $msg; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error_msg)): ?>
                        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="form-group">
                            <label for="category_id">Category <span class="text-danger">*</span></label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <option value="">Select a Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="content">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="image">Featured Image (Optional)</label>
                            <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                        </div>

                        <button type="submit" class="btn btn-primary">Submit for Review</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
mysqli_close($conn);
require_once 'includes/footer.php';
?>
