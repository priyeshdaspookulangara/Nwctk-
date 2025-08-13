<?php
$page_title = "Our Blog";
require_once 'includes/header.php';
require_once '../includes/db.php';

$posts = [];
$sql = "SELECT bp.id, bp.title, bp.content, bp.image_url, bp.author, bp.created_at, bc.name as category_name, bc.id as category_id
        FROM blog_posts bp
        LEFT JOIN blog_categories bc ON bp.category_id = bc.id
        ORDER BY bp.created_at DESC";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (strpos($row['image_url'], '../public/') === 0) {
            $row['image_url'] = substr($row['image_url'], strlen('../public/'));
        } elseif (strpos($row['image_url'], 'public/') === 0) {
             $row['image_url'] = substr($row['image_url'], strlen('public/'));
        }
        $posts[] = $row;
    }
}
mysqli_free_result($result);
mysqli_close($conn);
?>

<div class="page-header">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <p class="lead">Insights, stories, and updates from our team.</p>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <?php if (empty($posts)): ?>
                <p class="text-center">No blog posts found at the moment. Please check back soon!</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="card mb-4 shadow-sm">
                        <div class="row no-gutters">
                            <div class="col-md-4">
                                <?php
                                $post_image_url = !empty($post['image_url']) ? htmlspecialchars(rtrim($path_to_base_url_for_assets, '/') . '/' . $post['image_url']) : 'https://via.placeholder.com/300x200.png?text=Blog+Post';
                                ?>
                                <a href="blog_post.php?id=<?php echo $post['id']; ?>">
                                    <img src="<?php echo $post_image_url; ?>" class="card-img" alt="<?php echo htmlspecialchars($post['title']); ?>" style="height: 220px; object-fit: cover;">
                                </a>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="blog_post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                                    </h5>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            Published on: <?php echo date("F j, Y", strtotime($post['created_at'])); ?> |
                                            Category: <a href="blog_category.php?id=<?php echo $post['category_id']; ?>"><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></a>
                                            <?php if (!empty($post['author'])): ?>
                                                | Author: <?php echo htmlspecialchars($post['author']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </p>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 250) . (strlen($post['content']) > 250 ? '...' : ''))); ?></p>
                                    <a href="blog_post.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-secondary btn-sm">Read More</a>
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
