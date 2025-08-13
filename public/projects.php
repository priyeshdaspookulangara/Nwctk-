<?php
$page_title = "Our Projects";
require_once 'includes/header.php';
require_once '../includes/db.php';

$projects = [];
$sql = "SELECT id, name, description, start_date, end_date, status, image_url FROM projects ORDER BY start_date DESC, name ASC";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (strpos($row['image_url'], '../public/') === 0) {
            $row['image_url'] = substr($row['image_url'], strlen('../public/'));
        } elseif (strpos($row['image_url'], 'public/') === 0) {
             $row['image_url'] = substr($row['image_url'], strlen('public/'));
        }
        $projects[] = $row;
    }
}
mysqli_free_result($result);
mysqli_close($conn);
?>

<div class="page-header">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <p class="lead">Discover the impactful projects we are undertaking.</p>
    </div>
</div>

<div class="container mt-4">
    <?php if (empty($projects)): ?>
        <p class="text-center">No projects found at the moment. Please check back soon!</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($projects as $project): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php
                        $project_image_url = !empty($project['image_url']) ? htmlspecialchars(rtrim($path_to_base_url_for_assets, '/') . '/' . $project['image_url']) : 'https://via.placeholder.com/350x200.png?text=Project';
                        ?>
                        <img src="<?php echo $project_image_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($project['name']); ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($project['name']); ?></h5>
                            <p class="card-text flex-grow-1"><?php echo nl2br(htmlspecialchars(substr($project['description'], 0, 150) . (strlen($project['description']) > 150 ? '...' : ''))); ?></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    Status: <span class="font-weight-bold"><?php echo htmlspecialchars($project['status']); ?></span><br>
                                    <?php if ($project['start_date']): ?>
                                        Start Date: <?php echo date("M Y", strtotime($project['start_date'])); ?>
                                    <?php endif; ?>
                                    <?php if ($project['end_date']): ?>
                                        | End Date: <?php echo date("M Y", strtotime($project['end_date'])); ?>
                                    <?php endif; ?>
                                </small>
                            </p>
                             <?php // Detail page link can be added later e.g., project_detail.php?id=<?php echo $project['id']; ?>
                            <a href="#" class="btn btn-outline-primary mt-auto align-self-start">Learn More (Details page not yet implemented)</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
