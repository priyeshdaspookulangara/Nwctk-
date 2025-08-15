<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Edit About Us Page Content";
$page_name_db = "about_us";
$msg = "";
$error_msg = "";

// Default structure for about us content
$about_us_default = [
    'introduction' => 'Default introduction text. Please update this.',
    'vision' => 'Default vision text. Please update this.',
    'mission' => 'Default mission text. Please update this.',
    'philosophy' => 'Default philosophy text. Please update this.',
    'history' => 'Default history text. Please update this.'
];

// Fetch existing content
$sql_fetch = "SELECT content FROM page_content WHERE page_name = '" . sanitize_input($conn, $page_name_db) . "'";
$result_fetch = mysqli_query($conn, $sql_fetch);

if ($result_fetch && mysqli_num_rows($result_fetch) > 0) {
    $row = mysqli_fetch_assoc($result_fetch);
    $about_content = json_decode($row['content'], true);

    // Check if content is a string (legacy) or null/invalid JSON, then convert
    if (!is_array($about_content)) {
        $legacy_content = $about_content; // Keep the old text for the introduction
        $about_content = $about_us_default;
        if (!empty($legacy_content) && is_string($legacy_content)) {
            $about_content['introduction'] = $legacy_content;
        }
    } else {
        // Ensure all keys from default are present
        $about_content = array_merge($about_us_default, $about_content);
    }
} else {
    $about_content = $about_us_default;
    $json_content_default = json_encode($about_us_default, JSON_PRETTY_PRINT);
    $sql_insert_placeholder = "INSERT INTO page_content (page_name, content) VALUES ('" . sanitize_input($conn, $page_name_db) . "', '" . sanitize_input($conn, $json_content_default) . "')";
    if(mysqli_query($conn, $sql_insert_placeholder)) {
        $msg = "Initial About Us content created. Please edit below.";
    } else {
        $error_msg = "Error creating placeholder content: " . mysqli_error($conn);
    }
}
if($result_fetch) mysqli_free_result($result_fetch);

// Handle content update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['about_content_update'])) {
    $updated_content = [];
    foreach ($about_us_default as $key => $default_value) {
        $updated_content[$key] = $_POST[$key] ?? $default_value;
    }

    $json_content = json_encode($updated_content, JSON_PRETTY_PRINT);
    $json_content_db = sanitize_input($conn, $json_content);

    $sql_update = "UPDATE page_content SET content = '{$json_content_db}' WHERE page_name = '" . sanitize_input($conn, $page_name_db) . "'";
    if (mysqli_query($conn, $sql_update)) {
        $msg = "About Us page content updated successfully!";
        $about_content = $updated_content;
    } else {
        $error_msg = "Error updating content: " . mysqli_error($conn);
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
                    <div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo $msg; ?><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo $error_msg; ?><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="introduction">Introduction</label>
                        <textarea class="form-control" id="introduction" name="introduction" rows="5"><?php echo htmlspecialchars($about_content['introduction']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="vision">Vision</label>
                        <textarea class="form-control" id="vision" name="vision" rows="3"><?php echo htmlspecialchars($about_content['vision']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="mission">Mission</label>
                        <textarea class="form-control" id="mission" name="mission" rows="3"><?php echo htmlspecialchars($about_content['mission']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="philosophy">Philosophy</label>
                        <textarea class="form-control" id="philosophy" name="philosophy" rows="5"><?php echo htmlspecialchars($about_content['philosophy']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="history">History</label>
                        <textarea class="form-control" id="history" name="history" rows="5"><?php echo htmlspecialchars($about_content['history']); ?></textarea>
                    </div>

                    <button type="submit" name="about_content_update" class="btn btn-primary">Save Content</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
