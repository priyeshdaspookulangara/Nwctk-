<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Manage News & Events";
$msg = "";
$error_msg = "";

// Handle Delete Action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        // Get image_url to delete the file
        $sql_select_image = "SELECT image_url FROM news_events WHERE id = " . $delete_id;
        $result_image = mysqli_query($conn, $sql_select_image);
        if ($result_image && mysqli_num_rows($result_image) > 0) {
            $row_image = mysqli_fetch_assoc($result_image);
            $image_to_delete_web = $row_image['image_url'];

            if (!empty($image_to_delete_web)) {
                $image_to_delete_server = realpath(dirname(__FILE__) . '/' . $image_to_delete_web);
                if ($image_to_delete_server && file_exists($image_to_delete_server)) {
                    unlink($image_to_delete_server);
                }
            }
        }
        mysqli_free_result($result_image);

        // Delete the record
        $sql_delete = "DELETE FROM news_events WHERE id = " . $delete_id;
        if (mysqli_query($conn, $sql_delete)) {
            $msg = "Item deleted successfully.";
        } else {
            $error_msg = "Error deleting item: " . mysqli_error($conn);
        }
    } else {
        $error_msg = "Invalid ID for deletion.";
    }
}

// Fetch all news and events
$items = [];
$sql = "SELECT id, title, image_url, date, type, created_at FROM news_events ORDER BY date DESC, created_at DESC";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    mysqli_free_result($result);
}

?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?php echo $page_title; ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Admin</a></li>
                    <li class="breadcrumb-item active">News & Events</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">List of News & Events</h3>
                <div class="card-tools">
                    <a href="add_news.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Item
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <?php if (empty($items)): ?>
                    <div class="alert alert-info">No news or events found. <a href="add_news.php">Add one now</a>.</div>
                <?php else: ?>
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Added On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count = 1; foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td>
                                    <?php if (!empty($item['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" style="width: 70px; height: auto; border-radius: 3px;">
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($item['type'])); ?></td>
                                <td><?php echo date("d M, Y", strtotime($item['date'])); ?></td>
                                <td><?php echo date("d M, Y", strtotime($item['created_at'])); ?></td>
                                <td>
                                    <a href="edit_news.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this item? This action cannot be undone.');">
                                        <input type="hidden" name="delete_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
