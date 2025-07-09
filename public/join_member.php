<?php
$page_title = "Become a Member";
require_once 'includes/header.php';
// require_once '../includes/db.php'; // Will be needed for form submission

$form_msg = "";
$form_error_msg = "";

// Placeholder for form submission logic (to be implemented in step 17)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_member_request'])) {
    require_once '../includes/db.php'; // Path to DB from public/join_member.php

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $reason_to_join = trim($_POST['reason_to_join'] ?? '');

    // Validation
    if (empty($full_name)) {
        $form_error_msg .= "Full Name is required.<br>";
    }
    if (empty($email)) {
        $form_error_msg .= "Email Address is required.<br>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error_msg .= "Invalid Email Address format.<br>";
    }
     // Phone validation (basic example: check if not empty and numeric if you want to enforce)
    if (!empty($phone) && !preg_match('/^[0-9\s\-\+\(\)]+$/', $phone)) {
        $form_error_msg .= "Invalid phone number format.<br>";
    }


    if (empty($form_error_msg)) {
        $full_name_db = sanitize_input($conn, $full_name);
        $email_db = sanitize_input($conn, $email);
        $phone_db = sanitize_input($conn, $phone);
        $address_db = sanitize_input($conn, $address);
        $reason_to_join_db = sanitize_input($conn, $reason_to_join);

        $sql = "INSERT INTO member_requests (full_name, email, phone, address, reason_to_join, status, submitted_at) VALUES (
                '" . $full_name_db . "',
                '" . $email_db . "',
                '" . $phone_db . "',
                '" . $address_db . "',
                '" . $reason_to_join_db . "',
                'pending',
                NOW()
            )";

        if (mysqli_query($conn, $sql)) {
            $form_msg = "Thank you for your interest in becoming a member! Your request has been submitted and is pending review.";
            $_POST = []; // Clear form
        } else {
            $form_error_msg = "Sorry, there was an error submitting your request. Please try again later. Error: " . mysqli_error($conn);
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
        <p class="lead">Join our community and support our cause by becoming a member.</p>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2>Membership Request Form</h2>
            <p>Fill out the form below to apply for membership. We'll review your application and get back to you soon.</p>

            <?php if (!empty($form_msg)): ?>
                <div class="alert alert-success"><?php echo $form_msg; ?></div>
            <?php endif; ?>
            <?php if (!empty($form_error_msg)): ?>
                <div class="alert alert-danger"><?php echo $form_error_msg; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="memberRequestForm">
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
                    <label for="address">Address (Optional)</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="reason_to_join">Reason for Joining (Optional)</label>
                    <textarea class="form-control" id="reason_to_join" name="reason_to_join" rows="4"><?php echo isset($_POST['reason_to_join']) ? htmlspecialchars($_POST['reason_to_join']) : ''; ?></textarea>
                </div>
                <button type="submit" name="submit_member_request" class="btn btn-primary">Submit Request</button>
            </form>
            <p class="mt-3"><small><em>Note: Full form submission and database integration will be completed in a later step.</em></small></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
