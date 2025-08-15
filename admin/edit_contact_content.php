<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Edit Contact Page Content";
$page_name_db = "contact";
$msg = "";
$error_msg = "";

// Default contact info structure
$contact_info = [
    'ngo_name' => '',
    'address' => '',
    'phone' => '',
    'email' => '',
    'website' => '',
    'office_hours_line1' => '',
    'office_hours_line2' => ''
];

// Fetch existing content
$sql_fetch = "SELECT content FROM page_content WHERE page_name = '" . sanitize_input($conn, $page_name_db) . "'";
$result_fetch = mysqli_query($conn, $sql_fetch);
if ($result_fetch && mysqli_num_rows($result_fetch) > 0) {
    $row = mysqli_fetch_assoc($result_fetch);
    $decoded_content = json_decode($row['content'], true);
    if (is_array($decoded_content)) {
        $contact_info = array_merge($contact_info, $decoded_content);
    }
}

// Handle content update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['page_content_update'])) {
    $new_contact_info = [
        'ngo_name' => $_POST['ngo_name'],
        'address' => $_POST['address'],
        'phone' => $_POST['phone'],
        'email' => $_POST['email'],
        'website' => $_POST['website'],
        'office_hours_line1' => $_POST['office_hours_line1'],
        'office_hours_line2' => $_POST['office_hours_line2']
    ];

    $json_content = json_encode($new_contact_info);
    $sanitized_json_content = sanitize_input($conn, $json_content);

    $check_sql = "SELECT id FROM page_content WHERE page_name = '" . sanitize_input($conn, $page_name_db) . "'";
    $check_result = mysqli_query($conn, $check_sql);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $update_sql = "UPDATE page_content SET content = '" . $sanitized_json_content . "' WHERE page_name = '" . sanitize_input($conn, $page_name_db) . "'";
        if (mysqli_query($conn, $update_sql)) {
            $msg = "Contact page content updated successfully!";
            $contact_info = $new_contact_info;
        } else {
            $error_msg = "Error updating content: " . mysqli_error($conn);
        }
    } else {
        $insert_sql = "INSERT INTO page_content (page_name, content) VALUES ('" . sanitize_input($conn, $page_name_db) . "', '" . $sanitized_json_content . "')";
        if (mysqli_query($conn, $insert_sql)) {
            $msg = "Contact page content saved successfully!";
            $contact_info = $new_contact_info;
        } else {
            $error_msg = "Error saving new content: " . mysqli_error($conn);
        }
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
                    <li class="breadcrumb-item active">Contact Page</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Content for Contact Page</h3>
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
                        <label for="ngo_name">NGO Name</label>
                        <input type="text" class="form-control" id="ngo_name" name="ngo_name" value="<?php echo htmlspecialchars($contact_info['ngo_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($contact_info['address']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($contact_info['phone']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($contact_info['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="text" class="form-control" id="website" name="website" value="<?php echo htmlspecialchars($contact_info['website']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="office_hours_line1">Office Hours Line 1</label>
                        <input type="text" class="form-control" id="office_hours_line1" name="office_hours_line1" value="<?php echo htmlspecialchars($contact_info['office_hours_line1']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="office_hours_line2">Office Hours Line 2</label>
                        <input type="text" class="form-control" id="office_hours_line2" name="office_hours_line2" value="<?php echo htmlspecialchars($contact_info['office_hours_line2']); ?>">
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
