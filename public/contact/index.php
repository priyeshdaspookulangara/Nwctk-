<?php
$page_title = "Contact Us";
// The header include path needs to be adjusted because this file is in a subdirectory
require_once '../includes/header.php';
// Database connection
require_once '../../includes/db.php';

// Default contact details structure
$contact_details = [
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

// Fetch dynamic contact details from the database
$page_name_db = "contact_us";
$sql_fetch = "SELECT content FROM page_content WHERE page_name = '" . sanitize_input($conn, $page_name_db) . "'";
$result_fetch = mysqli_query($conn, $sql_fetch);
if ($result_fetch && mysqli_num_rows($result_fetch) > 0) {
    $row = mysqli_fetch_assoc($result_fetch);
    $db_details = json_decode($row['content'], true);
    // Merge with default to ensure all keys exist
    $contact_details = array_merge($contact_details, $db_details ?? []);
}
if($result_fetch) mysqli_free_result($result_fetch);


$form_msg = "";
$form_error_msg = "";

// Form submission handling
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_inquiry'])) {
    // The DB connection is already open. We need to make sure we don't try to open it again.
    // We will also not close it here, but let the script end to close it or close it in the footer.
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation
    if (empty($name)) {
        $form_error_msg .= "Full Name is required.<br>";
    }
    if (empty($email)) {
        $form_error_msg .= "Email Address is required.<br>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error_msg .= "Invalid Email Address format.<br>";
    }
    if (empty($message)) {
        $form_error_msg .= "Message is required.<br>";
    }
    // Basic length check for subject as an example
    if (!empty($subject) && strlen($subject) > 255) {
        $form_error_msg .= "Subject is too long (max 255 characters).<br>";
    }


    if (empty($form_error_msg)) {
        // Sanitize inputs for DB
        $name_db = sanitize_input($conn, $name);
        $email_db = sanitize_input($conn, $email);
        $phone_db = sanitize_input($conn, $phone);
        $subject_db = sanitize_input($conn, $subject);
        $message_db = sanitize_input($conn, $message);

        $sql = "INSERT INTO inquiries (name, email, phone, subject, message, status, submitted_at) VALUES (
                '" . $name_db . "',
                '" . $email_db . "',
                '" . $phone_db . "',
                '" . $subject_db . "',
                '" . $message_db . "',
                'new',
                NOW()
            )";

        if (mysqli_query($conn, $sql)) {
            $form_msg = "Thank you for your inquiry! We have received your message and will get back to you soon.";
            $_POST = []; // Clear form fields on success
        } else {
            $form_error_msg = "Sorry, there was an error submitting your inquiry. Please try again later. Error: " . mysqli_error($conn);
        }
        // Let the script close the connection at the end
    }
}
?>

<div class="page-header">
    <div class="container">
        <h1>Contact Us</h1>
        <p class="lead">We'd love to hear from you. Reach out with any questions or inquiries.</p>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-7">
            <h2>Send Us a Message</h2>
            <p>Use the form below to get in touch with us directly. We aim to respond within 24-48 hours.</p>

            <?php if (!empty($form_msg)): ?>
                <div class="alert alert-success"><?php echo $form_msg; ?></div>
            <?php endif; ?>
            <?php if (!empty($form_error_msg)): ?>
                <div class="alert alert-danger"><?php echo $form_error_msg; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="enquiryForm">
                <div class="form-group">
                    <label for="name">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number (Optional)</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="message">Message <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>
                <button type="submit" name="submit_inquiry" class="btn btn-primary">Send Inquiry</button>
            </form>
        </div>
        <div class="col-md-5">
            <h2>Our Contact Information</h2>
            <address>
                <strong><?php echo htmlspecialchars($contact_details['ngo_name']); ?></strong><br>
                <?php echo htmlspecialchars($contact_details['address']); ?><br>
                <?php echo htmlspecialchars($contact_details['city_state_zip']); ?><br>
                <?php echo htmlspecialchars($contact_details['country']); ?>
            </address>
            <p><i class="fas fa-phone mr-2"></i> <?php echo htmlspecialchars($contact_details['phone']); ?></p>
            <p><i class="fas fa-envelope mr-2"></i> <a href="mailto:<?php echo htmlspecialchars($contact_details['email']); ?>"><?php echo htmlspecialchars($contact_details['email']); ?></a></p>
            <p><i class="fas fa-globe mr-2"></i> <a href="http://<?php echo htmlspecialchars($contact_details['website']); ?>" target="_blank"><?php echo htmlspecialchars($contact_details['website']); ?></a></p>

            <h3 class="mt-4">Office Hours</h3>
            <p>Monday - Friday: <?php echo htmlspecialchars($contact_details['office_hours_mf']); ?></p>
            <p>Saturday - Sunday: <?php echo htmlspecialchars($contact_details['office_hours_ss']); ?></p>

            <!-- Placeholder for Google Map -->
            <div class="mt-4">
                <h5>Our Location</h5>
                <div id="map-placeholder" style="height: 250px; background-color: #e9ecef;" class="d-flex align-items-center justify-content-center">
                    <p class="text-muted">Google Map will be embedded here.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// The footer include path also needs to be adjusted
require_once '../includes/footer.php';
?>
