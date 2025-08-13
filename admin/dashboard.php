<?php
// Include auth_check.php to ensure user is logged in
require_once '../includes/auth_check.php'; // Adjust path as necessary
require_once '../includes/db.php';       // Database connection

// --- Temporary Migration Logic ---
// This will create the blog tables if they don't exist.
// This is a workaround for not having shell access to run migration scripts.
$migration_sql = "
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `image_url` VARCHAR(255) NULL,
  `author` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `blog_categories`(`id`) ON DELETE SET NULL
);
";

if (!mysqli_multi_query($conn, $migration_sql)) {
    // Handle error if migration fails - for now, we'll just log it.
    error_log("Failed to apply migration: " . mysqli_error($conn));
} else {
    // To ensure all results from multi_query are consumed
    while (mysqli_next_result($conn)) {;}
}
// --- End of Temporary Migration Logic ---


// Page specific logic can go here. For dashboard, it might be stats.
// For now, it's a simple welcome page.

// Example: Get some counts (optional)
$counts = [
    'trustees' => 0,
    'news_events' => 0,
    'projects' => 0,
    'inquiries' => 0
];

$result_trustees = mysqli_query($conn, "SELECT COUNT(*) as count FROM trustees");
if($result_trustees && mysqli_num_rows($result_trustees) > 0) $counts['trustees'] = mysqli_fetch_assoc($result_trustees)['count'];

$result_news = mysqli_query($conn, "SELECT COUNT(*) as count FROM news_events");
if($result_news && mysqli_num_rows($result_news) > 0) $counts['news_events'] = mysqli_fetch_assoc($result_news)['count'];

$result_projects = mysqli_query($conn, "SELECT COUNT(*) as count FROM projects");
if($result_projects && mysqli_num_rows($result_projects) > 0) $counts['projects'] = mysqli_fetch_assoc($result_projects)['count'];

$result_inquiries = mysqli_query($conn, "SELECT COUNT(*) as count FROM inquiries WHERE status = 'new'");
if($result_inquiries && mysqli_num_rows($result_inquiries) > 0) $counts['inquiries'] = mysqli_fetch_assoc($result_inquiries)['count'];

mysqli_close($conn);

?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Admin</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users-cog"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Trustees</span>
                        <span class="info-box-number"><?php echo $counts['trustees']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-newspaper"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">News & Events</span>
                        <span class="info-box-number"><?php echo $counts['news_events']; ?></span>
                    </div>
                </div>
            </div>
            <!-- fix for small devices only -->
            <div class="clearfix hidden-md-up"></div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-project-diagram"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Projects</span>
                        <span class="info-box-number"><?php echo $counts['projects']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-envelope"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">New Inquiries</span>
                        <span class="info-box-number"><?php echo $counts['inquiries']; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->

        <div class="jumbotron">
            <h1 class="display-4">Welcome, <?php echo htmlspecialchars($_SESSION["admin_username"]); ?>!</h1>
            <p class="lead">This is the central control panel for managing the NGO website content.</p>
            <hr class="my-4">
            <p>You can manage trustees, news, projects, and other site content using the navigation menu on the left.</p>
            <a class="btn btn-primary btn-lg" href="manage_news.php" role="button">Manage News</a>
        </div>

        <!-- Further dashboard elements can be added here -->
        <!-- e.g., quick links, recent activity, charts -->

    </div><!--/. container-fluid -->
</section>
<!-- /.content -->

<?php include 'includes/footer.php'; ?>

<style>
/* Specific styles for dashboard info boxes if AdminLTE is not fully used */
.info-box {
    box-shadow: 0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2);
    border-radius: .25rem;
    background-color: #fff;
    display: flex;
    margin-bottom: 1rem;
    min-height: 80px;
    padding: .5rem;
    position: relative;
    width: 100%;
}
.info-box .info-box-icon {
    border-radius: .25rem;
    align-items: center;
    display: flex;
    font-size: 1.875rem;
    justify-content: center;
    text-align: center;
    width: 70px;
    color: #fff !important; /* Ensure icon color is white for bg-* classes */
}
.info-box .info-box-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.8;
    flex: 1;
    padding: 0 10px;
}
.info-box .info-box-text, .info-box .progress-description {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.info-box .info-box-number {
    display: block;
    margin-top: .25rem;
    font-weight: 700;
}
.elevation-1 {
    box-shadow: 0 1px 3px rgba(0,0,0,.12),0 1px 2px rgba(0,0,0,.24)!important;
}
.bg-info { background-color: #17a2b8!important; }
.bg-danger { background-color: #dc3545!important; }
.bg-success { background-color: #28a745!important; }
.bg-warning { background-color: #ffc107!important; color: #1f2d3d!important; /* Text color for warning */ }
.bg-warning .info-box-icon i { color: #fff !important; /* Ensure icons on warning bg are visible */ }


.content-header {
    padding: 15px 0.5rem;
}
.content-header h1 {
    font-size: 1.8rem;
    margin: 0;
}
.breadcrumb {
    background-color: transparent;
    padding: 0;
    margin-bottom: 0;
}
</style>
