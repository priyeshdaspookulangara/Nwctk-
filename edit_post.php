<?php
// Initialize the session
session_start();

// Include config file
require_once "config.php";

// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Define variables
$post = null;
$post_id = 0;
$title = $content = "";
$title_err = $content_err = "";

// Process post ID from URL, ensure it's an integer
$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($post_id === false) {
    header("location: index.php");
    exit;
}

// Fetch the post data to check ownership and fill the form
$link_fetch = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
$sql_fetch = "SELECT id, title, content, user_id FROM posts WHERE id = ?";
if($stmt_fetch = mysqli_prepare($link_fetch, $sql_fetch)){
    mysqli_stmt_bind_param($stmt_fetch, "i", $post_id);
    if(mysqli_stmt_execute($stmt_fetch)){
        $result = mysqli_stmt_get_result($stmt_fetch);
        if(mysqli_num_rows($result) == 1){
            $post = mysqli_fetch_assoc($result);
            // Authorization check: user must be the author or an admin
            if($_SESSION['id'] !== $post['user_id'] && $_SESSION['role'] !== 'admin'){
                header("location: index.php");
                exit;
            }
            $title = $post['title'];
            $content = $post['content'];
        } else {
            header("location: index.php");
            exit;
        }
    }
    mysqli_stmt_close($stmt_fetch);
}
mysqli_close($link_fetch);


// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate title
    if(empty(trim($_POST["title"]))){
        $title_err = "Please enter a title.";
    } else{
        $title = trim($_POST["title"]);
    }

    // Validate content
    if(empty(trim($_POST["content"]))){
        $content_err = "Please enter the content.";
    } else{
        $content = trim($_POST["content"]);
    }

    // If no errors, update the database
    if(empty($title_err) && empty($content_err)){
        $new_status = ($_SESSION['role'] === 'admin') ? 'published' : 'pending_approval';

        $link_update = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        $sql_update = "UPDATE posts SET title = ?, content = ?, status = ? WHERE id = ?";
        if($stmt_update = mysqli_prepare($link_update, $sql_update)){
            mysqli_stmt_bind_param($stmt_update, "sssi", $title, $content, $new_status, $post_id);
            if(mysqli_stmt_execute($stmt_update)){
                header("location: view_post.php?id=" . $post_id);
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt_update);
        }
        mysqli_close($link_update);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Post</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <h2>Edit Post</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $post_id; ?>" method="post">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($title); ?>">
                <span class="invalid-feedback"><?php echo $title_err; ?></span>
            </div>
            <div class="form-group">
                <label>Content</label>
                <textarea name="content" rows="10" class="form-control <?php echo (!empty($content_err)) ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($content); ?></textarea>
                <span class="invalid-feedback"><?php echo $content_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Save Changes">
                <a href="view_post.php?id=<?php echo $post_id; ?>" class="btn btn-secondary ml-2">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
