<?php
$page_title = "Photo Gallery";
require_once 'includes/header.php';
require_once '../includes/db.php';

$photos = [];
// Example: Filter by tag if a tag is provided in URL, e.g., gallery.php?tag=event2023
$filter_tag = isset($_GET['tag']) ? sanitize_input($conn, $_GET['tag']) : null;

$sql = "SELECT id, title, description, image_url, gallery_tag FROM photos ";
if ($filter_tag) {
    $sql .= " WHERE gallery_tag = '" . $filter_tag . "' ";
}
$sql .= " ORDER BY uploaded_at DESC";

$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (strpos($row['image_url'], '../public/') === 0) {
            $row['image_url'] = substr($row['image_url'], strlen('../public/'));
        } elseif (strpos($row['image_url'], 'public/') === 0) {
             $row['image_url'] = substr($row['image_url'], strlen('public/'));
        }
        $photos[] = $row;
    }
}
mysqli_free_result($result);

// Get unique tags for filtering (optional)
$tags = [];
$sql_tags = "SELECT DISTINCT gallery_tag FROM photos WHERE gallery_tag IS NOT NULL AND gallery_tag != '' ORDER BY gallery_tag ASC";
$result_tags = mysqli_query($conn, $sql_tags);
if($result_tags && mysqli_num_rows($result_tags) > 0) {
    while($tag_row = mysqli_fetch_assoc($result_tags)){
        $tags[] = $tag_row['gallery_tag'];
    }
}
mysqli_free_result($result_tags);
mysqli_close($conn);
?>
<!-- Ekko Lightbox CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.css">

<div class="page-header">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <p class="lead">Moments captured from our events, projects, and activities.
            <?php if ($filter_tag) echo " (Filtered by tag: <strong>" . htmlspecialchars($filter_tag) . "</strong>)"; ?>
        </p>
    </div>
</div>

<div class="container mt-4">
    <?php if (!empty($tags)): ?>
    <div class="text-center mb-4">
        Filter by tag:
        <a href="gallery.php" class="btn btn-sm btn-outline-secondary <?php echo !$filter_tag ? 'active' : ''; ?>">All</a>
        <?php foreach($tags as $tag): ?>
            <a href="gallery.php?tag=<?php echo urlencode($tag); ?>"
               class="btn btn-sm btn-outline-secondary <?php echo ($filter_tag === $tag) ? 'active' : ''; ?>">
               <?php echo htmlspecialchars($tag); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($photos)): ?>
        <p class="text-center">No photos found<?php if ($filter_tag) echo " for the tag '" . htmlspecialchars($filter_tag) . "'"; ?>. Please check back soon!</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($photos as $photo): ?>
                <?php $photo_url_display = htmlspecialchars(rtrim($path_to_base_url_for_assets, '/') . '/' . $photo['image_url']); ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <a href="<?php echo $photo_url_display; ?>"
                           data-toggle="lightbox"
                           data-gallery="ngo-gallery"
                           data-title="<?php echo htmlspecialchars($photo['title']); ?>"
                           data-footer="<?php echo htmlspecialchars($photo['description']); ?>">
                            <img src="<?php echo $photo_url_display; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($photo['title']); ?>" style="height: 200px; object-fit: cover;">
                        </a>
                        <div class="card-body">
                            <h6 class="card-title mb-1"><?php echo htmlspecialchars($photo['title']); ?></h6>
                            <?php if($photo['gallery_tag']): ?>
                                <p class="card-text"><small class="text-muted">Tag: <a href="gallery.php?tag=<?php echo urlencode($photo['gallery_tag']); ?>"><?php echo htmlspecialchars($photo['gallery_tag']); ?></a></small></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

<!-- Ekko Lightbox JS (ensure jQuery and Popper are loaded before this in footer.php) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.min.js"></script>
<script>
$(document).ready(function () {
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
       event.preventDefault();
       $(this).ekkoLightbox({
           alwaysShowClose: true,
           // You can add more options here if needed
       });
    });
});
// This JS is better placed in script.js but included here for gallery-specific functionality.
// If moving to script.js, ensure Ekko Lightbox JS is loaded before script.js or use a DOMContentLoaded wrapper.
</script>
