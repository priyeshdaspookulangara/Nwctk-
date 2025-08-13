<?php
$page_title = "Our Camps";
require_once 'includes/header.php';
require_once '../includes/db.php';

$camps = [];
$sql = "SELECT id, name, description, location, start_date, end_date, image_url FROM camps ORDER BY start_date DESC, name ASC";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (strpos($row['image_url'], '../public/') === 0) {
            $row['image_url'] = substr($row['image_url'], strlen('../public/'));
        } elseif (strpos($row['image_url'], 'public/') === 0) {
             $row['image_url'] = substr($row['image_url'], strlen('public/'));
        }
        $camps[] = $row;
    }
}
mysqli_free_result($result);
mysqli_close($conn);
?>

<div class="page-header">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <p class="lead">Information about our various camps and activities.</p>
    </div>
</div>

<div class="container mt-4">
    <?php if (empty($camps)): ?>
        <p class="text-center">No camps scheduled at the moment. Please check back soon!</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($camps as $camp): ?>
                <div class="col-md-12 mb-4">
                    <div class="card shadow-sm">
                        <div class="row no-gutters">
                            <div class="col-md-4">
                                <?php
                                $camp_image_url = !empty($camp['image_url']) ? htmlspecialchars(rtrim($path_to_base_url_for_assets, '/') . '/' . $camp['image_url']) : 'https://via.placeholder.com/700x400.png?text=Camp+Image';
                                ?>
                                <img src="<?php echo $camp_image_url; ?>" class="card-img" alt="<?php echo htmlspecialchars($camp['name']); ?>" style="height: 220px; object-fit: cover;">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($camp['name']); ?></h5>
                                    <p class="card-text"><small class="text-muted">
                                        <?php if($camp['location']): ?>
                                            Location: <?php echo htmlspecialchars($camp['location']); ?> <br>
                                        <?php endif; ?>
                                        <?php if ($camp['start_date']): ?>
                                            Dates: <?php echo date("F j, Y", strtotime($camp['start_date'])); ?>
                                            <?php if ($camp['end_date'] && $camp['end_date'] != $camp['start_date']): ?>
                                                - <?php echo date("F j, Y", strtotime($camp['end_date'])); ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            Dates: To be Announced
                                        <?php endif; ?>
                                    </small></p>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($camp['description'], 0, 200) . (strlen($camp['description']) > 200 ? '...' : ''))); ?></p>
                                    <?php // Detail page link can be added later e.g., camp_detail.php?id=<?php echo $camp['id']; ?>
                                    <a href="#" class="btn btn-outline-info btn-sm">More Details (Details page not yet implemented)</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
