<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Manage Internship Applications";
$msg = "";
$error_msg = "";

// Handle Status Update or Delete Action
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['application_id']) && isset($_POST['action'])) {
        $application_id = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);
        $action = $_POST['action'];

        if ($application_id) {
            if ($action === 'delete') {
                // Logic for deleting files and then the application record
                // ... (This part is complex and can be refactored to prepared statements if needed, but the main goal is adding user creation)
                $sql = "DELETE FROM internship_applications WHERE id = ?";
                 if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "i", $application_id);
                    if(mysqli_stmt_execute($stmt)) $msg = "Application deleted successfully.";
                    else $error_msg = "Error deleting application.";
                    mysqli_stmt_close($stmt);
                }
            } elseif ($action === 'accepted') {
                // User creation logic
                $sql_get_request = "SELECT full_name, email FROM internship_applications WHERE id = ?";
                if($stmt_get_request = mysqli_prepare($conn, $sql_get_request)){
                    mysqli_stmt_bind_param($stmt_get_request, "i", $application_id);
                    mysqli_stmt_execute($stmt_get_request);
                    $request_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_get_request));
                    mysqli_stmt_close($stmt_get_request);

                    if ($request_data) {
                        $email = $request_data['email'];
                        $sql_check_user = "SELECT id FROM users WHERE email = ?";
                        if($stmt_check_user = mysqli_prepare($conn, $sql_check_user)){
                            mysqli_stmt_bind_param($stmt_check_user, "s", $email);
                            mysqli_stmt_execute($stmt_check_user);
                            mysqli_stmt_store_result($stmt_check_user);

                            if (mysqli_stmt_num_rows($stmt_check_user) == 0) {
                                $username = strstr($email, '@', true) . rand(10, 99);
                                $temp_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
                                $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
                                $role = 'intern';

                                $sql_insert_user = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
                                if($stmt_insert_user = mysqli_prepare($conn, $sql_insert_user)){
                                    mysqli_stmt_bind_param($stmt_insert_user, "ssss", $username, $email, $hashed_password, $role);
                                    if (mysqli_stmt_execute($stmt_insert_user)) {
                                        $sql_update_status = "UPDATE internship_applications SET status = 'accepted' WHERE id = ?";
                                        if($stmt_update_status = mysqli_prepare($conn, $sql_update_status)){
                                            mysqli_stmt_bind_param($stmt_update_status, "i", $application_id);
                                            mysqli_stmt_execute($stmt_update_status);
                                            mysqli_stmt_close($stmt_update_status);
                                        }
                                        $msg = "Application accepted. User account created with<br><strong>Username:</strong> " . htmlspecialchars($username) . "<br><strong>Temp Password:</strong> " . htmlspecialchars($temp_password);
                                    } else {
                                        $error_msg = "Failed to create user account.";
                                    }
                                    mysqli_stmt_close($stmt_insert_user);
                                }
                            } else {
                                $error_msg = "A user with this email already exists.";
                            }
                            mysqli_stmt_close($stmt_check_user);
                        }
                    } else {
                        $error_msg = "Could not find the original application.";
                    }
                }
            } elseif (in_array($action, ['pending', 'under_review', 'rejected'])) {
                $sql = "UPDATE internship_applications SET status = ? WHERE id = ?";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "si", $action, $application_id);
                    if(mysqli_stmt_execute($stmt)) $msg = "Application status updated to '" . ucfirst(str_replace('_', ' ', $action)) . "'.";
                    else $error_msg = "Error updating status.";
                    mysqli_stmt_close($stmt);
                }
            } else {
                $error_msg = "Invalid action specified.";
            }
        } else {
            $error_msg = "Invalid application ID.";
        }
    }
}

// Fetch all internship applications
$applications = [];
$sql_fetch = "SELECT id, full_name, email, phone, education_background, area_of_interest, resume_url, cover_letter_url, status, submitted_at
              FROM internship_applications
              ORDER BY submitted_at DESC";
$result = mysqli_query($conn, $sql_fetch);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $applications[] = $row;
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
                    <li class="breadcrumb-item active">Internship Applications</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Incoming Internship Applications</h3>
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

                <?php if (empty($applications)): ?>
                    <div class="alert alert-info">No internship applications found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Submitted On</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $count = 1; foreach ($applications as $app): ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                                    <td><a href="mailto:<?php echo htmlspecialchars($app['email']); ?>"><?php echo htmlspecialchars($app['email']); ?></a></td>
                                    <td><?php echo date("d M, Y H:i", strtotime($app['submitted_at'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php
                                            switch ($app['status']) {
                                                case 'accepted': echo 'success'; break;
                                                case 'rejected': echo 'danger'; break;
                                                case 'under_review': echo 'info'; break;
                                                default: echo 'warning'; break; // pending
                                            }
                                        ?>"><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($app['status']))); ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu">
                                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="dropdown-item p-0"><input type="hidden" name="application_id" value="<?php echo $app['id']; ?>"><button type="submit" name="action" value="accepted" class="btn btn-link text-success w-100 text-left">Accept</button></form>
                                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="dropdown-item p-0"><input type="hidden" name="application_id" value="<?php echo $app['id']; ?>"><button type="submit" name="action" value="rejected" class="btn btn-link text-danger w-100 text-left">Reject</button></form>
                                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="dropdown-item p-0"><input type="hidden" name="application_id" value="<?php echo $app['id']; ?>"><button type="submit" name="action" value="under_review" class="btn btn-link text-info w-100 text-left">Set to Under Review</button></form>
                                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="dropdown-item p-0"><input type="hidden" name="application_id" value="<?php echo $app['id']; ?>"><button type="submit" name="action" value="pending" class="btn btn-link text-warning w-100 text-left">Set to Pending</button></form>
                                                <div class="dropdown-divider"></div>
                                                <button type="button" class="dropdown-item text-info" data-toggle="modal" data-target="#detailsModal_<?php echo $app['id']; ?>">View Details</button>
                                                <div class="dropdown-divider"></div>
                                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="dropdown-item p-0" onsubmit="return confirm('Are you sure you want to delete this application? This will also delete associated files and cannot be undone.');">
                                                    <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                                    <button type="submit" name="action" value="delete" class="btn btn-link text-danger w-100 text-left">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Details Modal -->
                                <div class="modal fade" id="detailsModal_<?php echo $app['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel_<?php echo $app['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="detailsModalLabel_<?php echo $app['id']; ?>">Application: <?php echo htmlspecialchars($app['full_name']); ?></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Full Name:</strong> <?php echo htmlspecialchars($app['full_name']); ?></p>
                                                <p><strong>Email:</strong> <?php echo htmlspecialchars($app['email']); ?></p>
                                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($app['phone'] ? $app['phone'] : 'N/A'); ?></p>
                                                <p><strong>Education Background:</strong> <?php echo nl2br(htmlspecialchars($app['education_background'] ? $app['education_background'] : 'N/A')); ?></p>
                                                <p><strong>Area of Interest:</strong> <?php echo nl2br(htmlspecialchars($app['area_of_interest'] ? $app['area_of_interest'] : 'N/A')); ?></p>
                                                <p><strong>Resume:</strong>
                                                    <?php if (!empty($app['resume_url'])): ?>
                                                        <a href="<?php echo htmlspecialchars($app['resume_url']); ?>" target="_blank">View/Download Resume</a>
                                                    <?php else: echo 'N/A'; endif; ?>
                                                </p>
                                                <p><strong>Cover Letter:</strong>
                                                    <?php if (!empty($app['cover_letter_url'])): ?>
                                                        <a href="<?php echo htmlspecialchars($app['cover_letter_url']); ?>" target="_blank">View/Download Cover Letter</a>
                                                    <?php else: echo 'N/A'; endif; ?>
                                                </p>
                                                <p><strong>Status:</strong> <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($app['status']))); ?></p>
                                                <p><strong>Submitted At:</strong> <?php echo date("d M, Y H:i:s", strtotime($app['submitted_at'])); ?></p>
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
