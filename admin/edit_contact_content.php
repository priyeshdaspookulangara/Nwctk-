<?php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$page_title = "Edit Contact Page Content";
$page_name_db = "contact_us";
$msg = "";
$error_msg = "";

// Default contact details structure
$contact_details_default = [
    'ngo_name' => '[NGO Name]',
    'address' => '123 Philanthropy Drive',
    'city_state_zip' => 'Cityville, State 54321',
    'country' => 'Country',
    'phone' => '(123) 456-7890',
    'email' => 'info@ngoname.org',
    'website' => 'www.ngoname.org',
    'office_hours_mf' => '9:00 AM - 5:00 PM',
    'office_hours_ss' => 'Closed'
];

// Fetch existing content
$sql_fetch = "SELECT content FROM page_content WHERE page_name = '" . sanitize_input($conn, $page_name_db) . "'";
$result_fetch = mysqli_query($conn, $sql_fetch);

if ($result_fetch && mysqli_num_rows($result_fetch) > 0) {
    $row = mysqli_fetch_assoc($result_fetch);
    $contact_details = json_decode($row['content'], true);
    // Ensure all keys from default are present
    $contact_details = array_merge($contact_details_default, $contact_details ?? []);
} else {
    // If no record exists, use default details and prepare to insert
    $contact_details = $contact_details_default;
    $json_content_default = json_encode($contact_details_default, JSON_PRETTY_PRINT);
    $sql_insert_placeholder = "INSERT INTO page_content (page_name, content) VALUES ('" . sanitize_input($conn, $page_name_db) . "', '" . sanitize_input($conn, $json_content_default) . "')";
    if(mysqli_query($conn, $sql_insert_placeholder)) {
        $msg = "Initial contact details created. Please edit below.";
    } else {
        $error_msg = "Error creating placeholder content: " . mysqli_error($conn);
    }
}
if($result_fetch) mysqli_free_result($result_fetch);

// Handle content update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_details_update'])) {
    // Sanitize and prepare data from POST
    $updated_details = [];
    foreach ($contact_details_default as $key => $default_value) {
        $updated_details[$key] = $_POST[$key] ?? $default_value;
    }

    // Convert to JSON
    $json_content = json_encode($updated_details, JSON_PRETTY_PRINT);
    $json_content_db = sanitize_input($conn, $json_content);

    // Update the database
    $sql_update = "UPDATE page_content SET content = '{$json_content_db}' WHERE page_name = '" . sanitize_input($conn, $page_name_db) . "'";
    if (mysqli_query($conn, $sql_update)) {
        $msg = "Contact page details updated successfully!";
        $contact_details = $updated_details; // Refresh displayed data
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
                <h3 class="card-title">Edit Details for Contact Information Section</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo $msg; ?><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo $error_msg; ?><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="ngo_name">NGO Name</label>
                            <input type="text" class="form-control" id="ngo_name" name="ngo_name" value="<?php echo htmlspecialchars($contact_details['ngo_name']); ?>">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($contact_details['phone']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($contact_details['email']); ?>">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="website">Website URL</label>
                            <input type="text" class="form-control" id="website" name="website" value="<?php echo htmlspecialchars($contact_details['website']); ?>">
                        </div>
                    </div>
                     <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($contact_details['address']); ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="city_state_zip">City, State, Zip Code</label>
                            <input type="text" class="form-control" id="city_state_zip" name="city_state_zip" value="<?php echo htmlspecialchars($contact_details['city_state_zip']); ?>">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="country">Country</label>
                            <input type="text" class.form-control id="country" name="country" value="<?php echo htmlspecialchars($contact_details['country']); ?>">
                        </div>
                    </div>
                    <hr>
                    <h4>Office Hours</h4>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="office_hours_mf">Monday - Friday</label>
                            <input type="text" class="form-control" id="office_hours_mf" name="office_hours_mf" value="<?php echo htmlspecialchars($contact_details['office_hours_mf']); ?>">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="office_hours_ss">Saturday - Sunday</label>
                            <input type="text" class="form-control" id="office_hours_ss" name="office_hours_ss" value="<?php echo htmlspecialchars($contact_details['office_hours_ss']); ?>">
                        </div>
                    </div>

                    <button type="submit" name="contact_details_update" class="btn btn-primary">Save Details</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
