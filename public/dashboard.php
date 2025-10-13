<?php
session_start();

// If the user is not logged in, redirect to the login page.
if (!isset($_SESSION["user_loggedin"]) || $_SESSION["user_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$page_title = "User Dashboard";
require_once 'includes/header.php';
require_once '../includes/db.php';

$user_id = $_SESSION["user_id"];
$user_role = $_SESSION["user_role"];
$user_username = $_SESSION["user_username"];

// Contributor roles
$contributor_roles = ['member', 'volunteer', 'intern'];

?>

<div class="page-header">
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($user_username); ?>!</h1>
        <p class="lead">This is your personal dashboard.</p>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    Navigation
                </div>
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action active">Dashboard Home</a>
                    <?php if (in_array($user_role, $contributor_roles)): ?>
                        <a href="add_user_post.php" class="list-group-item list-group-item-action">Add New Blog Post</a>
                        <a href="manage_user_posts.php" class="list-group-item list-group-item-action">My Blog Posts</a>
                    <?php endif; ?>
                    <a href="#" class="list-group-item list-group-item-action">My Profile</a>
                    <a href="logout.php" class="list-group-item list-group-item-action text-danger">Logout</a>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Dashboard Overview
                </div>
                <div class="card-body">
                    <h5 class="card-title">Your Details</h5>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user_username); ?></p>
                    <p><strong>Role:</strong> <?php echo ucfirst(htmlspecialchars($user_role)); ?></p>
                    <hr>
                    <?php if (in_array($user_role, $contributor_roles)): ?>
                        <h5 class="card-title">Contributor Actions</h5>
                        <p>You have the ability to write and submit blog posts for review by our admin team.</p>
                        <a href="add_user_post.php" class="btn btn-primary">Write a New Post</a>
                    <?php else: ?>
                        <h5 class="card-title">Welcome to our community!</h5>
                        <p>You can now like and comment on blog posts. Thank you for being a part of our journey!</p>
                        <a href="blog.php" class="btn btn-primary">Go to Blog</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
mysqli_close($conn);
require_once 'includes/footer.php';
?>
