<?php
$page_title = "Testimonials";
require_once 'includes/header.php';
require_once '../includes/db.php';

$testimonials = [];
$sql = "SELECT author_name, author_position, testimonial_text, image_url, rating FROM testimonials ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (strpos($row['image_url'], '../public/') === 0) {
            $row['image_url'] = substr($row['image_url'], strlen('../public/'));
        } elseif (strpos($row['image_url'], 'public/') === 0) {
             $row['image_url'] = substr($row['image_url'], strlen('public/'));
        }
        $testimonials[] = $row;
    }
}
mysqli_free_result($result);
mysqli_close($conn);

function display_stars_frontend($rating) {
    $stars_html = '';
    if ($rating === null || $rating == 0) return ''; // No stars if no rating
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars_html .= '<i class="fas fa-star text-warning"></i>'; // Full star
        } else {
            // Check for half star, e.g. if rating is 3.5 and i is 4
            // For simplicity, this example only handles full stars.
            // To handle half stars, rating could be float and logic adjusted.
            $stars_html .= '<i class="far fa-star text-muted"></i>'; // Empty star (muted color)
        }
    }
    return $stars_html;
}
?>

<div class="page-header">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <p class="lead">Hear what people are saying about our work and impact.</p>
    </div>
</div>

<div class="container mt-4">
    <?php if (empty($testimonials)): ?>
        <p class="text-center">No testimonials found at the moment. Please check back soon!</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <?php if (!empty($testimonial['image_url'])):
                                    $author_image_url = htmlspecialchars(rtrim($path_to_base_url_for_assets, '/') . '/' . $testimonial['image_url']);
                                ?>
                                    <img src="<?php echo $author_image_url; ?>" alt="<?php echo htmlspecialchars($testimonial['author_name']); ?>"
                                         class="rounded-circle mr-3" style="width: 60px; height: 60px; object-fit: cover;">
                                <?php else: ?>
                                     <div class="rounded-circle mr-3 bg-secondary d-flex justify-content-center align-items-center text-white" style="width: 60px; height: 60px; font-size:1.5rem;">
                                        <i class="fas fa-user"></i>
                                     </div>
                                <?php endif; ?>
                                <blockquote class="blockquote mb-0 flex-grow-1">
                                    <p class="mb-2">"<?php echo nl2br(htmlspecialchars($testimonial['testimonial_text'])); ?>"</p>
                                    <footer class="blockquote-footer mt-0">
                                        <?php echo htmlspecialchars($testimonial['author_name']); ?>
                                        <?php if (!empty($testimonial['author_position'])): ?>
                                            <cite title="Position">, <?php echo htmlspecialchars($testimonial['author_position']); ?></cite>
                                        <?php endif; ?>
                                    </footer>
                                </blockquote>
                            </div>
                             <?php
                                $stars = display_stars_frontend($testimonial['rating']);
                                if (!empty($stars)):
                            ?>
                                <div class="text-right mt-2">
                                    <?php echo $stars; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
