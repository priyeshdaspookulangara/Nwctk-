<?php
$page_title = "Home";
require_once 'includes/header.php';
require_once '../includes/db.php';

// Fetch banners
$sql_banners = "SELECT * FROM banners WHERE is_active = TRUE ORDER BY display_order ASC";
$result_banners = mysqli_query($conn, $sql_banners);
$banners = mysqli_fetch_all($result_banners, MYSQLI_ASSOC);

// Fetch home page sections
$sql_sections = "SELECT * FROM home_page_sections WHERE is_active = TRUE ORDER BY display_order ASC";
$result_sections = mysqli_query($conn, $sql_sections);
$sections = [];
while ($row = mysqli_fetch_assoc($result_sections)) {
    $sections[$row['section_name']] = $row;
}

mysqli_close($conn);
?>

<!-- Banners Section -->
<?php if (!empty($banners)) : ?>
    <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <?php foreach ($banners as $i => $banner) : ?>
                <li data-target="#carouselExampleIndicators" data-slide-to="<?php echo $i; ?>" class="<?php echo $i === 0 ? 'active' : ''; ?>"></li>
            <?php endforeach; ?>
        </ol>
        <div class="carousel-inner">
            <?php foreach ($banners as $i => $banner) : ?>
                <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                    <img src="<?php echo htmlspecialchars($banner['image_url']); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($banner['title']); ?>">
                    <div class="carousel-caption d-none d-md-block">
                        <h5><?php echo htmlspecialchars($banner['title']); ?></h5>
                        <p><?php echo htmlspecialchars($banner['subtitle']); ?></p>
                        <?php if (!empty($banner['link_url'])) : ?>
                            <a href="<?php echo htmlspecialchars($banner['link_url']); ?>" class="btn btn-primary">Learn More</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
<?php endif; ?>

<div class="container mt-5">
    <!-- Introduction Section -->
    <?php if (isset($sections['introduction'])) : ?>
        <section id="introduction" class="mb-5">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="text-center"><?php echo htmlspecialchars($sections['introduction']['title']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($sections['introduction']['content'])); ?></p>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Why Us Section -->
    <?php if (isset($sections['why_us'])) : ?>
        <section id="why-us" class="bg-light p-5 rounded mb-5">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="text-center"><?php echo htmlspecialchars($sections['why_us']['title']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($sections['why_us']['content'])); ?></p>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Activities Section -->
    <?php if (isset($sections['activities'])) : ?>
        <section id="activities" class="mb-5">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="text-center"><?php echo htmlspecialchars($sections['activities']['title']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($sections['activities']['content'])); ?></p>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Donation Section -->
    <section id="donation" class="text-center bg-primary text-white p-5 rounded">
        <h2>Support Our Cause</h2>
        <p class="lead">Your contribution can make a significant impact on the lives of many.</p>
        <a href="donate/" class="btn btn-lg btn-light">Donate Now</a>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>
