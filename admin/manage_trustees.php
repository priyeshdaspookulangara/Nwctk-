<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Manage Trustees";
$msg = ""; // For success messages
$error_msg = ""; // For error messages

// Handle Delete Action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        // First, get the image_url to delete the file
        $sql_select_image = "SELECT image_url FROM trustees WHERE id = " . $delete_id;
        $result_image = mysqli_query($conn, $sql_select_image);
        if ($result_image && mysqli_num_rows($result_image) > 0) {
            $row_image = mysqli_fetch_assoc($result_image);
            $image_to_delete_web = $row_image['image_url']; // e.g., ../public/uploads/trustees/image.jpg

            // Convert web path to server path for deletion
            // Assuming $image_to_delete_web path starts with '../public/'
            // and this script is in 'admin/'
            if (!empty($image_to_delete_web)) {
                $image_to_delete_server = realpath(dirname(__FILE__) . '/' . $image_to_delete_web);
                if ($image_to_delete_server && file_exists($image_to_delete_server)) {
                    unlink($image_to_delete_server);
                }
            }
        }
        mysqli_free_result($result_image);

        // Then delete the record
        $sql_delete = "DELETE FROM trustees WHERE id = " . $delete_id;
        if (mysqli_query($conn, $sql_delete)) {
            $msg = "Trustee deleted successfully.";
        } else {
            $error_msg = "Error deleting trustee: " . mysqli_error($conn);
        }
    } else {
        $error_msg = "Invalid ID for deletion.";
    }
}

// Fetch all trustees
$trustees = [];
$sql = "SELECT id, name, position, image_url, display_order, created_at FROM trustees ORDER BY display_order ASC, name ASC";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $trustees[] = $row;
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
                    <li class="breadcrumb-item active">Trustees</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">List of Trustees</h3>
                <div class="card-tools">
                    <a href="add_trustee.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Trustee
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

                <?php if (empty($trustees)): ?>
                    <div class="alert alert-info">No trustees found. <a href="add_trustee.php">Add one now</a>.</div>
                <?php else: ?>
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Order</th>
                                <th>Added On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count = 1; foreach ($trustees as $trustee): ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td>
                                    <?php if (!empty($trustee['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($trustee['image_url']); ?>" alt="<?php echo htmlspecialchars($trustee['name']); ?>" style="width: 50px; height: auto; border-radius: 3px;">
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($trustee['name']); ?></td>
                                <td><?php echo htmlspecialchars($trustee['position']); ?></td>
                                <td><?php echo htmlspecialchars($trustee['display_order']); ?></td>
                                <td><?php echo date("d M, Y", strtotime($trustee['created_at'])); ?></td>
                                <td>
                                    <a href="edit_trustee.php?id=<?php echo $trustee['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this trustee? This action cannot be undone.');">
                                        <input type="hidden" name="delete_id" value="<?php echo $trustee['id']; ?>">
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
