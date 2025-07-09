<?php
$page_title = "News & Events";
require_once 'includes/header.php';
require_once '../includes/db.php'; // Path to DB from public/news.php

$news_items = [];
$sql = "SELECT id, title, content, image_url, date, type FROM news_events ORDER BY date DESC, created_at DESC";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Adjust image_url path
        if (strpos($row['image_url'], '../public/') === 0) {
            $row['image_url'] = substr($row['image_url'], strlen('../public/'));
        } elseif (strpos($row['image_url'], 'public/') === 0) {
             $row['image_url'] = substr($row['image_url'], strlen('public/'));
        }
        $news_items[] = $row;
    }
}
mysqli_free_result($result);
mysqli_close($conn);
?>

<div class="page-header">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <p class="lead">Stay updated with our latest news and upcoming events.</p>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <?php if (empty($news_items)): ?>
                <p class="text-center">No news or events found at the moment. Please check back soon!</p>
            <?php else: ?>
                <?php foreach ($news_items as $item): ?>
                    <div class="card mb-4 shadow-sm">
                        <div class="row no-gutters">
                            <div class="col-md-4">
                                <?php
                                $item_image_url = !empty($item['image_url']) ? htmlspecialchars(rtrim($path_to_base_url_for_assets, '/') . '/' . $item['image_url']) : 'https://via.placeholder.com/300x200.png?text=' . ucfirst($item['type']);
                                ?>
                                <img src="<?php echo $item_image_url; ?>" class="card-img" alt="<?php echo htmlspecialchars($item['title']); ?>" style="height: 200px; object-fit: cover;">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            Date: <?php echo date("F j, Y", strtotime($item['date'])); ?> |
                                            Type: <span class="badge badge-<?php echo ($item['type'] == 'event' ? 'info' : 'primary'); ?>"><?php echo ucfirst(htmlspecialchars($item['type'])); ?></span>
                                        </small>
                                    </p>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($item['content'], 0, 250) . (strlen($item['content']) > 250 ? '...' : ''))); ?></p>
                                    <?php // Detail page link can be added later e.g., news_detail.php?id=<?php echo $item['id']; ?>
                                    <a href="#" class="btn btn-outline-secondary btn-sm">Read More (Details page not yet implemented)</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
