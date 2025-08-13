<?php
require_once '../includes/db.php';

$post_id = null;
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $post_id = $_GET['id'];
} else {
    // Redirect to blog index if ID is not valid or not present
    header("Location: blog.php");
    exit;
}

$post = null;
$sql = "SELECT bp.id, bp.title, bp.content, bp.image_url, bp.author, bp.created_at, bc.name as category_name, bc.id as category_id
        FROM blog_posts bp
        LEFT JOIN blog_categories bc ON bp.category_id = bc.id
        WHERE bp.id = ?";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) == 1) {
        $post = mysqli_fetch_assoc($result);
        if (strpos($post['image_url'], '../public/') === 0) {
            $post['image_url'] = substr($post['image_url'], strlen('../public/'));
        } elseif (strpos($post['image_url'], 'public/') === 0) {
             $post['image_url'] = substr($post['image_url'], strlen('public/'));
        }
    } else {
        // Post not found, redirect
        header("Location: blog.php?error=notfound");
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    die("Error preparing statement: " . mysqli_error($conn));
}
mysqli_close($conn);

$page_title = htmlspecialchars($post['title']);
require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <p class="lead">
            Published on <?php echo date("F j, Y", strtotime($post['created_at'])); ?>
            in <a href="blog_category.php?id=<?php echo $post['category_id']; ?>"><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></a>
            <?php if (!empty($post['author'])): ?>
                by <?php echo htmlspecialchars($post['author']); ?>
            <?php endif; ?>
        </p>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="blog-post-content">
                <?php if (!empty($post['image_url'])): ?>
                    <?php $post_image_url = htmlspecialchars(rtrim($path_to_base_url_for_assets, '/') . '/' . $post['image_url']); ?>
                    <img src="<?php echo $post_image_url; ?>" class="img-fluid rounded mb-4" alt="<?php echo $page_title; ?>" style="width: 100%;">
                <?php endif; ?>

                <div class="post-body">
                    <?php echo nl2br($post['content']); // Using nl2br to respect newlines. For full HTML, remove htmlspecialchars from insert/update and just output content. ?>
                </div>

                <hr class="my-4">

                <a href="blog.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left"></i> Back to Blog</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<style>
.blog-post-content .post-body {
    font-size: 1.1rem;
    line-height: 1.7;
}
</style>
