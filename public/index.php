<?php
$page_title = "Home";
require_once 'includes/header.php';
// Later, we will fetch dynamic content from `page_content` table for 'home'
require_once '../includes/db.php'; // Path to DB from public/index.php

$home_content = "Welcome to our NGO! This is the default home page content. It will be replaced by dynamic content from the database soon.";
$sql_page_content = "SELECT content FROM page_content WHERE page_name = 'home'";
$result_page_content = mysqli_query($conn, $sql_page_content);
if ($result_page_content && mysqli_num_rows($result_page_content) > 0) {
    $row_content = mysqli_fetch_assoc($result_page_content);
    $home_content = !empty($row_content['content']) ? $row_content['content'] : $home_content;
}
mysqli_close($conn);
?>

<div class="page-header">
    <div class="container">
        <h1>Welcome to [NGO Name]</h1>
        <p class="lead">Making a difference, one step at a time.</p>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <?php echo nl2br(htmlspecialchars_decode($home_content)); // Use htmlspecialchars_decode if content has HTML, otherwise just nl2br ?>
            <!-- Placeholder for dynamic content like latest news, upcoming events -->
        </div>
    </div>

    <hr class="my-5">

    <section id="quick-links" class="mb-5">
        <h2 class="text-center mb-4">Explore Our Work</h2>
        <div class="row text-center">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <i class="fas fa-newspaper fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Latest News</h5>
                        <p class="card-text">Stay updated with our recent activities and announcements.</p>
                        <a href="news.php" class="btn btn-outline-primary">Read More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <i class="fas fa-project-diagram fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Our Projects</h5>
                        <p class="card-text">Discover the impactful projects we are currently running.</p>
                        <a href="projects.php" class="btn btn-outline-success">View Projects</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                 <div class="card">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Get Involved</h5>
                        <p class="card-text">Find out how you can contribute and be a part of our mission.</p>
                        <a href="join_volunteer.php" class="btn btn-outline-info">Join Us</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                 <div class="card">
                    <div class="card-body">
                        <i class="fas fa-images fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Gallery</h5>
                        <p class="card-text">See moments from our events, camps, and projects.</p>
                        <a href="gallery.php" class="btn btn-outline-warning">View Gallery</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Placeholder for a simple call to action -->
    <section id="call-to-action" class="text-center bg-light p-5 rounded mb-5">
        <h2>Support Our Cause</h2>
        <p class="lead">Your contribution can make a significant impact on the lives of many.</p>
        <a href="#" class="btn btn-lg btn-success">Donate Now</a>
        <a href="contact/" class="btn btn-lg btn-outline-secondary">Contact Us</a>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>
