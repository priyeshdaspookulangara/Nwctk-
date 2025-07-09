<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Edit About Us Page Content";
$page_name_db = "about_us"; // Identifier for this page in the database
$msg = "";
$error_msg = "";
$content = "";

// Fetch existing content
$sql_fetch = "SELECT content FROM page_content WHERE page_name = '" . sanitize_input($conn, $page_name_db) . "'";
$result_fetch = mysqli_query($conn, $sql_fetch);
if ($result_fetch && mysqli_num_rows($result_fetch) > 0) {
    $row = mysqli_fetch_assoc($result_fetch);
    $content = $row['content'];
} elseif ($result_fetch) {
    // Page entry doesn't exist, create it with placeholder
    $placeholder_content = "Default About Us Page Content. Please edit this.";
    $sql_insert_placeholder = "INSERT INTO page_content (page_name, content) VALUES ('" . sanitize_input($conn, $page_name_db) . "', '" . sanitize_input($conn, $placeholder_content) . "')";
    if(mysqli_query($conn, $sql_insert_placeholder)) {
        $content = $placeholder_content;
        $msg = "Initial content placeholder created. Please edit below.";
    } else {
        $error_msg = "Error creating placeholder content: " . mysqli_error($conn);
    }
} else {
    $error_msg = "Error fetching page content: " . mysqli_error($conn);
}
if($result_fetch) mysqli_free_result($result_fetch);


// Handle content update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['page_content_update'])) {
        $new_content = sanitize_input($conn, $_POST['content_area']); // Sanitize what goes into DB

        $check_sql = "SELECT id FROM page_content WHERE page_name = '" . sanitize_input($conn, $page_name_db) . "'";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $update_sql = "UPDATE page_content SET content = '" . $new_content . "' WHERE page_name = '" . sanitize_input($conn, $page_name_db) . "'";
            if (mysqli_query($conn, $update_sql)) {
                $msg = "About Us page content updated successfully!";
                $content = $_POST['content_area'];
            } else {
                $error_msg = "Error updating content: " . mysqli_error($conn);
            }
        } else {
            $insert_sql = "INSERT INTO page_content (page_name, content) VALUES ('" . sanitize_input($conn, $page_name_db) . "', '" . $new_content . "')";
            if (mysqli_query($conn, $insert_sql)) {
                $msg = "About Us page content saved successfully!";
                $content = $_POST['content_area'];
            } else {
                $error_msg = "Error saving new content: " . mysqli_error($conn);
            }
        }
        if($check_result) mysqli_free_result($check_result);
    }
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
                    <li class="breadcrumb-item"><a href="#">Page Content</a></li>
                    <li class="breadcrumb-item active">About Us Page</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Content for About Us Page</h3>
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

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="content_area">Page Content</label>
                        <textarea class="form-control" id="content_area" name="content_area" rows="15"><?php echo htmlspecialchars($content); ?></textarea>
                        <small class="form-text text-muted">You can use basic HTML tags for formatting. Ensure your HTML is valid.</small>
                    </div>

                    <button type="submit" name="page_content_update" class="btn btn-primary">Save Content</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
