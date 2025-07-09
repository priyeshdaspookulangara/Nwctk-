<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Manage Camps";
$msg = "";
$error_msg = "";

// Handle Delete Action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        // Get image_url to delete the file
        $sql_select_image = "SELECT image_url FROM camps WHERE id = " . $delete_id;
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
        $sql_delete = "DELETE FROM camps WHERE id = " . $delete_id;
        if (mysqli_query($conn, $sql_delete)) {
            $msg = "Camp deleted successfully.";
        } else {
            $error_msg = "Error deleting camp: " . mysqli_error($conn);
        }
    } else {
        $error_msg = "Invalid ID for deletion.";
    }
}

// Fetch all camps
$camps = [];
$sql = "SELECT id, name, location, start_date, end_date, image_url FROM camps ORDER BY start_date DESC, name ASC";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $camps[] = $row;
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
                    <li class="breadcrumb-item active">Camps</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">List of Camps</h3>
                <div class="card-tools">
                    <a href="add_camp.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Camp
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

                <?php if (empty($camps)): ?>
                    <div class="alert alert-info">No camps found. <a href="add_camp.php">Add one now</a>.</div>
                <?php else: ?>
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count = 1; foreach ($camps as $camp): ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td>
                                    <?php if (!empty($camp['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($camp['image_url']); ?>" alt="<?php echo htmlspecialchars($camp['name']); ?>" style="width: 70px; height: auto; border-radius: 3px;">
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($camp['name']); ?></td>
                                <td><?php echo htmlspecialchars($camp['location']); ?></td>
                                <td><?php echo $camp['start_date'] ? date("d M, Y", strtotime($camp['start_date'])) : 'N/A'; ?></td>
                                <td><?php echo $camp['end_date'] ? date("d M, Y", strtotime($camp['end_date'])) : 'N/A'; ?></td>
                                <td>
                                    <a href="edit_camp.php?id=<?php echo $camp['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this camp? This action cannot be undone.');">
                                        <input type="hidden" name="delete_id" value="<?php echo $camp['id']; ?>">
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
