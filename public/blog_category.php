<?php
require_once '../includes/db.php';

$category_id = null;
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $category_id = $_GET['id'];
} else {
    // Redirect to blog index if ID is not valid or not present
    header("Location: blog.php");
    exit;
}

// Fetch category details
$category = null;
$sql_cat = "SELECT name FROM blog_categories WHERE id = ?";
if ($stmt_cat = mysqli_prepare($conn, $sql_cat)) {
    mysqli_stmt_bind_param($stmt_cat, "i", $category_id);
    mysqli_stmt_execute($stmt_cat);
    $result_cat = mysqli_stmt_get_result($stmt_cat);
    if ($result_cat && mysqli_num_rows($result_cat) == 1) {
        $category = mysqli_fetch_assoc($result_cat);
    } else {
        header("Location: blog.php?error=catnotfound");
        exit;
    }
    mysqli_stmt_close($stmt_cat);
} else {
    die("Error preparing category fetch statement: " . mysqli_error($conn));
}


$page_title = "Posts in: " . htmlspecialchars($category['name']);
require_once 'includes/header.php';


$posts = [];
$sql_posts = "SELECT bp.id, bp.title, bp.content, bp.image_url, bp.author, bp.created_at, bc.name as category_name, bc.id as category_id
        FROM blog_posts bp
        LEFT JOIN blog_categories bc ON bp.category_id = bc.id
        WHERE bp.category_id = ?
        ORDER BY bp.created_at DESC";
if ($stmt_posts = mysqli_prepare($conn, $sql_posts)) {
    mysqli_stmt_bind_param($stmt_posts, "i", $category_id);
    mysqli_stmt_execute($stmt_posts);
    $result_posts = mysqli_stmt_get_result($stmt_posts);
    if ($result_posts) {
        while ($row = mysqli_fetch_assoc($result_posts)) {
            if (strpos($row['image_url'], '../public/') === 0) {
                $row['image_url'] = substr($row['image_url'], strlen('../public/'));
            } elseif (strpos($row['image_url'], 'public/') === 0) {
                 $row['image_url'] = substr($row['image_url'], strlen('public/'));
            }
            $posts[] = $row;
        }
    }
    mysqli_stmt_close($stmt_posts);
} else {
    // Optionally handle error
}
mysqli_close($conn);
?>

<div class="page-header">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <p class="lead">Showing all blog posts filed under this category.</p>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <?php if (empty($posts)): ?>
                <p class="text-center">No blog posts found in this category.</p>
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
                                            Published on: <?php echo date("F j, Y", strtotime($post['created_at'])); ?>
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
            <div class="mt-4">
                <a href="blog.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back to All Posts</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
