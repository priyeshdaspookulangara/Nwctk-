<?php
$page_title = "About Us";
require_once 'includes/header.php';
require_once '../includes/db.php';

$about_content = "Learn more about our mission, vision, and the dedicated team behind [NGO Name]. This content will be updated from the database.";
$sql_page_content = "SELECT content FROM page_content WHERE page_name = 'about_us'";
$result_page_content = mysqli_query($conn, $sql_page_content);
if ($result_page_content && mysqli_num_rows($result_page_content) > 0) {
    $row_content = mysqli_fetch_assoc($result_page_content);
    $about_content = !empty($row_content['content']) ? $row_content['content'] : $about_content;
}
if($result_page_content) mysqli_free_result($result_page_content);

// Fetch trustees
$trustees = [];
$sql_trustees = "SELECT name, position, image_url, bio FROM trustees ORDER BY display_order ASC, name ASC";
$result_trustees = mysqli_query($conn, $sql_trustees);
if ($result_trustees && mysqli_num_rows($result_trustees) > 0) {
    while ($row_trustee = mysqli_fetch_assoc($result_trustees)) {
        // Adjust image_url path for frontend display if it's stored relative to admin
        // Assuming image_url is like '../public/uploads/trustees/image.jpg' from admin context
        // From public/about.php, it needs to be 'uploads/trustees/image.jpg'
        if (strpos($row_trustee['image_url'], '../public/') === 0) {
            $row_trustee['image_url'] = substr($row_trustee['image_url'], strlen('../public/'));
        } elseif (strpos($row_trustee['image_url'], 'public/') === 0) { // If it was stored as public/ from root
             $row_trustee['image_url'] = substr($row_trustee['image_url'], strlen('public/'));
        }
        // If path_to_base_url_for_assets is available and correct, prepend it.
        // For now, assuming direct relative path from public root works.
        $trustees[] = $row_trustee;
    }
}
if($result_trustees) mysqli_free_result($result_trustees);

mysqli_close($conn);
?>

<div class="page-header">
    <div class="container">
        <h1>About [NGO Name]</h1>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <?php echo nl2br(htmlspecialchars_decode($about_content)); // Use htmlspecialchars_decode if content has HTML ?>
        </div>
    </div>

    <hr class="my-5">

    <section id="mission-vision" class="mb-5">
        <div class="row">
            <div class="col-md-6">
                <h3>Our Mission</h3>
                <p><em>Placeholder:</em> To empower underprivileged communities through sustainable development initiatives in education, health, and livelihood, fostering a society where every individual has the opportunity to achieve their full potential.</p>
            </div>
            <div class="col-md-6">
                <h3>Our Vision</h3>
                <p><em>Placeholder:</em> A world where compassion and collective action create equitable opportunities for all, leading to thriving, self-reliant communities and a just global society.</p>
            </div>
        </div>
    </section>

    <hr class="my-5">

    <section id="our-team" class="mb-5">
        <h2 class="text-center mb-4">Meet Our Trustees</h2>
        <?php if (!empty($trustees)): ?>
            <div class="row text-center justify-content-center">
                <?php foreach ($trustees as $trustee): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card h-100">
                            <img src="<?php echo !empty($trustee['image_url']) ? htmlspecialchars(rtrim($path_to_base_url_for_assets, '/') . '/' . $trustee['image_url']) : 'https://via.placeholder.com/200x200.png?text=Trustee'; ?>"
                                 class="card-img-top"
                                 alt="<?php echo htmlspecialchars($trustee['name']); ?>"
                                 style="height:200px; object-fit:cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($trustee['name']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($trustee['position']); ?></p>
                                <?php if(!empty($trustee['bio'])): ?>
                                    <p class="card-text"><small><?php echo nl2br(htmlspecialchars(substr($trustee['bio'], 0, 100) . (strlen($trustee['bio']) > 100 ? '...' : ''))); ?></small></p>
                                    <!-- Can add a modal to view full bio -->
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center">Information about our team will be updated soon.</p>
        <?php endif; ?>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>
