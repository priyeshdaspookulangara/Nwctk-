<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Manage Inquiries";
$msg = "";
$error_msg = "";

// Handle Status Update or Delete Action
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['inquiry_id']) && isset($_POST['action'])) {
        $inquiry_id = filter_input(INPUT_POST, 'inquiry_id', FILTER_VALIDATE_INT);
        $action = sanitize_input($conn, $_POST['action']); // 'new', 'read', 'replied', 'delete'

        if ($inquiry_id) {
            if ($action === 'delete') {
                $sql = "DELETE FROM inquiries WHERE id = " . $inquiry_id;
                if (mysqli_query($conn, $sql)) {
                    $msg = "Inquiry deleted successfully.";
                } else {
                    $error_msg = "Error deleting inquiry: " . mysqli_error($conn);
                }
            } elseif (in_array($action, ['new', 'read', 'replied'])) {
                $sql = "UPDATE inquiries SET status = '" . $action . "' WHERE id = " . $inquiry_id;
                if (mysqli_query($conn, $sql)) {
                    $msg = "Inquiry status updated to '" . ucfirst($action) . "'.";
                } else {
                    $error_msg = "Error updating status: " . mysqli_error($conn);
                }
            } else {
                $error_msg = "Invalid action specified.";
            }
        } else {
            $error_msg = "Invalid inquiry ID.";
        }
    }
}

// Fetch all inquiries
$inquiries = [];
$sql_fetch = "SELECT id, name, email, phone, subject, message, status, submitted_at
              FROM inquiries
              ORDER BY submitted_at DESC";
$result = mysqli_query($conn, $sql_fetch);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $inquiries[] = $row;
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
                    <li class="breadcrumb-item active">Inquiries</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Contact Form Inquiries</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo $msg; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo $error_msg; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($inquiries)): ?>
                    <div class="alert alert-info">No inquiries found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Submitted On</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $count = 1; foreach ($inquiries as $inq): ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td><?php echo htmlspecialchars($inq['name']); ?></td>
                                    <td><a href="mailto:<?php echo htmlspecialchars($inq['email']); ?>"><?php echo htmlspecialchars($inq['email']); ?></a></td>
                                    <td><?php echo htmlspecialchars($inq['subject'] ? $inq['subject'] : 'N/A'); ?></td>
                                    <td><?php echo date("d M, Y H:i", strtotime($inq['submitted_at'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php
                                            switch ($inq['status']) {
                                                case 'replied': echo 'success'; break;
                                                case 'read': echo 'info'; break;
                                                default: echo 'warning'; break; // new
                                            }
                                        ?>"><?php echo ucfirst(htmlspecialchars($inq['status'])); ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu">
                                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="dropdown-item p-0"><input type="hidden" name="inquiry_id" value="<?php echo $inq['id']; ?>"><button type="submit" name="action" value="replied" class="btn btn-link text-success w-100 text-left">Mark as Replied</button></form>
                                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="dropdown-item p-0"><input type="hidden" name="inquiry_id" value="<?php echo $inq['id']; ?>"><button type="submit" name="action" value="read" class="btn btn-link text-info w-100 text-left">Mark as Read</button></form>
                                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="dropdown-item p-0"><input type="hidden" name="inquiry_id" value="<?php echo $inq['id']; ?>"><button type="submit" name="action" value="new" class="btn btn-link text-warning w-100 text-left">Mark as New</button></form>
                                                <div class="dropdown-divider"></div>
                                                <button type="button" class="dropdown-item text-info" data-toggle="modal" data-target="#detailsModal_<?php echo $inq['id']; ?>">View Message</button>
                                                <div class="dropdown-divider"></div>
                                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="dropdown-item p-0" onsubmit="return confirm('Are you sure you want to delete this inquiry? This action cannot be undone.');">
                                                    <input type="hidden" name="inquiry_id" value="<?php echo $inq['id']; ?>">
                                                    <button type="submit" name="action" value="delete" class="btn btn-link text-danger w-100 text-left">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Details Modal -->
                                <div class="modal fade" id="detailsModal_<?php echo $inq['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel_<?php echo $inq['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="detailsModalLabel_<?php echo $inq['id']; ?>">Inquiry from: <?php echo htmlspecialchars($inq['name']); ?></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Name:</strong> <?php echo htmlspecialchars($inq['name']); ?></p>
                                                <p><strong>Email:</strong> <?php echo htmlspecialchars($inq['email']); ?></p>
                                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($inq['phone'] ? $inq['phone'] : 'N/A'); ?></p>
                                                <p><strong>Subject:</strong> <?php echo htmlspecialchars($inq['subject'] ? $inq['subject'] : 'N/A'); ?></p>
                                                <hr>
                                                <p><strong>Message:</strong></p>
                                                <p><?php echo nl2br(htmlspecialchars($inq['message'])); ?></p>
                                                <hr>
                                                <p><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($inq['status'])); ?></p>
                                                <p><strong>Submitted At:</strong> <?php echo date("d M, Y H:i:s", strtotime($inq['submitted_at'])); ?></p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
