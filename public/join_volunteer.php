<?php
$page_title = "Become a Volunteer";
require_once 'includes/header.php';
// require_once '../includes/db.php';

$form_msg = "";
$form_error_msg = "";

// Placeholder for form submission logic (to be implemented in step 17)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_volunteer_request'])) {
    require_once '../includes/db.php';

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $availability = trim($_POST['availability'] ?? '');
    $skills_interests = trim($_POST['skills_interests'] ?? '');

    // Validation
    if (empty($full_name)) {
        $form_error_msg .= "Full Name is required.<br>";
    }
    if (empty($email)) {
        $form_error_msg .= "Email Address is required.<br>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error_msg .= "Invalid Email Address format.<br>";
    }
    if (!empty($phone) && !preg_match('/^[0-9\s\-\+\(\)]+$/', $phone)) {
        $form_error_msg .= "Invalid phone number format.<br>";
    }

    if (empty($form_error_msg)) {
        $full_name_db = sanitize_input($conn, $full_name);
        $email_db = sanitize_input($conn, $email);
        $phone_db = sanitize_input($conn, $phone);
        $availability_db = sanitize_input($conn, $availability);
        $skills_interests_db = sanitize_input($conn, $skills_interests);

        $sql = "INSERT INTO volunteer_requests (full_name, email, phone, availability, skills_interests, status, submitted_at) VALUES (
                '" . $full_name_db . "',
                '" . $email_db . "',
                '" . $phone_db . "',
                '" . $availability_db . "',
                '" . $skills_interests_db . "',
                'pending',
                NOW()
            )";

        if (mysqli_query($conn, $sql)) {
            $form_msg = "Thank you for your interest in volunteering! Your application has been submitted and is pending review.";
            $_POST = []; // Clear form
        } else {
            $form_error_msg = "Sorry, there was an error submitting your application. Please try again later. Error: " . mysqli_error($conn);
        }
        mysqli_close($conn);
    } else {
         if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}
?>

<div class="page-header">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <p class="lead">Make a difference by volunteering your time and skills.</p>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2>Volunteer Application Form</h2>
            <p>We appreciate your interest in volunteering with us. Please fill out the form below, and we'll be in touch.</p>

            <?php if (!empty($form_msg)): ?>
                <div class="alert alert-success"><?php echo $form_msg; ?></div>
            <?php endif; ?>
            <?php if (!empty($form_error_msg)): ?>
                <div class="alert alert-danger"><?php echo $form_error_msg; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="volunteerRequestForm">
                <div class="form-group">
                    <label for="full_name">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
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
                    <label for="availability">Availability (e.g., Weekends, Specific days/hours)</label>
                    <input type="text" class="form-control" id="availability" name="availability" value="<?php echo isset($_POST['availability']) ? htmlspecialchars($_POST['availability']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="skills_interests">Skills & Interests (Optional)</label>
                    <textarea class="form-control" id="skills_interests" name="skills_interests" rows="4"><?php echo isset($_POST['skills_interests']) ? htmlspecialchars($_POST['skills_interests']) : ''; ?></textarea>
                </div>
                <button type="submit" name="submit_volunteer_request" class="btn btn-primary">Submit Application</button>
            </form>
            <p class="mt-3"><small><em>Note: Full form submission and database integration will be completed in a later step.</em></small></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
