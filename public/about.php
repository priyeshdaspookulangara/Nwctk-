<?php
$page_title = "About Us";
require_once 'includes/header.php';
require_once '../includes/db.php';

// Default structure for about us content
$about_content = [
    'introduction' => 'Welcome to our organization. Learn more about our work and impact.',
    'vision' => 'A world where every person has the opportunity to achieve their fullest potential.',
    'mission' => 'To empower communities through education, healthcare, and sustainable development.',
    'philosophy' => 'We believe in collaboration, transparency, and sustainable change.',
    'history' => 'Founded in [Year], our organization has been dedicated to making a difference for over [X] years.'
];

// Fetch and decode About Us content
$sql_page_content = "SELECT content FROM page_content WHERE page_name = 'about_us'";
$result_page_content = mysqli_query($conn, $sql_page_content);
if ($result_page_content && mysqli_num_rows($result_page_content) > 0) {
    $row_content = mysqli_fetch_assoc($result_page_content);
    $db_content = json_decode($row_content['content'], true);
    if (is_array($db_content)) {
        $about_content = array_merge($about_content, $db_content);
    } elseif (!empty($row_content['content'])) {
        // Handle legacy string content
        $about_content['introduction'] = $row_content['content'];
    }
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
    <section id="introduction" class="mb-5">
        <div class="row">
            <div class="col-md-12">
                <h2>Introduction</h2>
                <p><?php echo nl2br(htmlspecialchars($about_content['introduction'])); ?></p>
            </div>
        </div>
    </section>

    <hr class="my-5">

    <section id="mission-vision" class="mb-5">
        <div class="row text-center">
            <div class="col-md-6">
                <h3><i class="fas fa-bullseye mr-2"></i>Our Mission</h3>
                <p><?php echo nl2br(htmlspecialchars($about_content['mission'])); ?></p>
            </div>
            <div class="col-md-6">
                <h3><i class="fas fa-eye mr-2"></i>Our Vision</h3>
                <p><?php echo nl2br(htmlspecialchars($about_content['vision'])); ?></p>
            </div>
        </div>
    </section>

    <hr class="my-5">

    <section id="philosophy" class="mb-5">
        <div class="row">
            <div class="col-md-12">
                <h2>Our Philosophy</h2>
                <p><?php echo nl2br(htmlspecialchars($about_content['philosophy'])); ?></p>
            </div>
        </div>
    </section>

    <hr class="my-5">

    <section id="history" class="mb-5">
        <div class="row">
            <div class="col-md-12">
                <h2>Our History</h2>
                <p><?php echo nl2br(htmlspecialchars($about_content['history'])); ?></p>
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
