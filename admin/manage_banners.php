<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Manage Banners";
$msg = "";
$error_msg = "";

// Handle Delete Action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        $sql_select_image = "SELECT image_url FROM banners WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql_select_image);
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        mysqli_stmt_execute($stmt);
        $result_image = mysqli_stmt_get_result($stmt);
        if ($row_image = mysqli_fetch_assoc($result_image)) {
            $image_to_delete = "../public/" . $row_image['image_url'];
            if (file_exists($image_to_delete)) {
                unlink($image_to_delete);
            }
        }
        mysqli_stmt_close($stmt);

        $sql_delete = "DELETE FROM banners WHERE id = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $delete_id);
        if (mysqli_stmt_execute($stmt_delete)) {
            $msg = "Banner deleted successfully.";
        } else {
            $error_msg = "Error deleting banner: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt_delete);
    }
}

// Fetch all banners
$banners = [];
$sql = "SELECT id, heading, sub_heading, image_url, display_order, created_at FROM banners ORDER BY display_order ASC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $banners[] = $row;
    }
    mysqli_free_result($result);
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><?php echo $page_title; ?></h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Admin</a></li>
                    <li class="breadcrumb-item active">Banners</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">List of Banners</h3>
                <div class="card-tools">
                    <a href="add_banner.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Banner</a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
                <?php if ($error_msg) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>

                <?php if (empty($banners)): ?>
                    <div class="alert alert-info">No banners found. <a href="add_banner.php">Add one now</a>.</div>
                <?php else: ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Heading</th>
                                <th>Sub-Heading</th>
                                <th>Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count = 1; foreach ($banners as $banner): ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td><img src="../public/<?php echo htmlspecialchars($banner['image_url']); ?>" alt="" style="width: 150px;"></td>
                                <td><?php echo htmlspecialchars($banner['heading']); ?></td>
                                <td><?php echo htmlspecialchars($banner['sub_heading']); ?></td>
                                <td><?php echo htmlspecialchars($banner['display_order']); ?></td>
                                <td>
                                    <a href="edit_banner.php?id=<?php echo $banner['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                    <form action="" method="post" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $banner['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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
