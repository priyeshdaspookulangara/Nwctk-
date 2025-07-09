<?php
$page_title = "Videos";
require_once 'includes/header.php';
require_once '../includes/db.php';

$videos = [];
$sql = "SELECT id, title, description, video_url, video_type, thumbnail_url, uploaded_at FROM videos ORDER BY uploaded_at DESC";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Auto-generate YouTube thumbnail if missing and type is YouTube
        if ($row['video_type'] === 'youtube' && empty($row['thumbnail_url'])) {
            $video_id = '';
            if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $row['video_url'], $matches)) {
                $video_id = $matches[1];
            } elseif (preg_match('/youtu\.be\/([^\&\?\/]+)/', $row['video_url'], $matches)) {
                $video_id = $matches[1];
            }
            if ($video_id) {
                $row['thumbnail_url'] = 'https://img.youtube.com/vi/' . $video_id . '/mqdefault.jpg';
            }
        }
        // Adjust other thumbnail paths if they are stored relative to admin/uploads
        elseif (!empty($row['thumbnail_url']) && strpos($row['thumbnail_url'], '../public/') === 0) {
             $row['thumbnail_url'] = substr($row['thumbnail_url'], strlen('../public/'));
        }  elseif (!empty($row['thumbnail_url']) && strpos($row['thumbnail_url'], 'public/') === 0) {
             $row['thumbnail_url'] = substr($row['thumbnail_url'], strlen('public/'));
        }
        $videos[] = $row;
    }
}
mysqli_free_result($result);
mysqli_close($conn);

function get_youtube_embed_url($url) {
    $video_id = '';
    if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $url, $matches)) {
        $video_id = $matches[1];
    } elseif (preg_match('/youtu\.be\/([^\&\?\/]+)/', $url, $matches)) {
        $video_id = $matches[1];
    }
    if ($video_id) {
        return 'https://www.youtube.com/embed/' . $video_id;
    }
    return null; // Or return original URL if not YouTube or not embeddable pattern
}

?>

<div class="page-header">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <p class="lead">Watch videos about our work, events, and stories.</p>
    </div>
</div>

<div class="container mt-4">
    <?php if (empty($videos)): ?>
        <p class="text-center">No videos found at the moment. Please check back soon!</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($videos as $video): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php
                        $embed_url = null;
                        if ($video['video_type'] === 'youtube') {
                            $embed_url = get_youtube_embed_url($video['video_url']);
                        }
                        // Add similar logic for Vimeo if needed:
                        // elseif ($video['video_type'] === 'vimeo') { $embed_url = get_vimeo_embed_url($video['video_url']); }

                        $thumbnail_display_url = !empty($video['thumbnail_url']) ? htmlspecialchars($video['thumbnail_url']) : 'https://via.placeholder.com/350x197.png?text=Video';
                        // If thumbnail_url might be relative from public root (like `uploads/videos/thumb.jpg`)
                        if (!empty($video['thumbnail_url']) && !filter_var($video['thumbnail_url'], FILTER_VALIDATE_URL)) {
                             $thumbnail_display_url = htmlspecialchars(rtrim($path_to_base_url_for_assets, '/') . '/' . $video['thumbnail_url']);
                        }

                        ?>
                        <div class="card-img-top embed-responsive embed-responsive-16by9">
                            <?php if ($embed_url && $video['video_type'] === 'youtube'): // Prioritize direct embed for YouTube ?>
                                <iframe class="embed-responsive-item" src="<?php echo htmlspecialchars($embed_url); ?>" allowfullscreen></iframe>
                            <?php else: ?>
                                <a href="<?php echo htmlspecialchars($video['video_url']); ?>" target="_blank">
                                    <img src="<?php echo $thumbnail_display_url; ?>" class="embed-responsive-item" alt="<?php echo htmlspecialchars($video['title']); ?>" style="object-fit: cover;">
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="<?php echo htmlspecialchars($video['video_url']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($video['title']); ?>
                                </a>
                            </h5>
                            <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($video['description'] ? substr($video['description'], 0, 100) . '...' : ''); ?></small></p>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted">Type: <?php echo ucfirst(htmlspecialchars($video['video_type'])); ?> | Uploaded: <?php echo date("M j, Y", strtotime($video['uploaded_at'])); ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
