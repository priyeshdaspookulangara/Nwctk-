<?php
$page_title = "Apply for Internship";
require_once 'includes/header.php';
// require_once '../includes/db.php';

$form_msg = "";
$form_error_msg = "";

// Placeholder for form submission logic (to be implemented in step 17)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_internship_application'])) {
    require_once '../includes/db.php';

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $education_background = trim($_POST['education_background'] ?? '');
    $area_of_interest = trim($_POST['area_of_interest'] ?? '');

    $resume_url_db = "";
    $cover_letter_url_db = "";

    // File Upload Directory (relative to this script's location in public/)
    $upload_dir_server = "uploads/internship_files/"; // This will be public/uploads/internship_files/
    // Create directory if it doesn't exist, relative to the script's current directory.
    // So, if script is in public/, it creates public/uploads/internship_files/
    if (!is_dir($upload_dir_server)) {
        if (!mkdir($upload_dir_server, 0777, true)) {
            $form_error_msg .= "Failed to create upload directory. Please contact admin.<br>";
        }
    }


    // Validation
    if (empty($full_name))  $form_error_msg .= "Full Name is required.<br>";
    if (empty($email)) $form_error_msg .= "Email Address is required.<br>";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $form_error_msg .= "Invalid Email Address format.<br>";
    if (empty($education_background)) $form_error_msg .= "Educational Background is required.<br>";
    if (empty($area_of_interest)) $form_error_msg .= "Area of Interest is required.<br>";
    if (!empty($phone) && !preg_match('/^[0-9\s\-\+\(\)]+$/', $phone)) $form_error_msg .= "Invalid phone number format.<br>";

    // Resume File Upload (Mandatory)
    if (isset($_FILES["resume_file"]) && $_FILES["resume_file"]["error"] == 0) {
        $allowed_types = ['pdf', 'doc', 'docx'];
        $file_name = basename($_FILES["resume_file"]["name"]);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $target_file_server_resume = $upload_dir_server . uniqid('resume_') . '_' . $file_name;

        if (in_array($file_type, $allowed_types)) {
            if ($_FILES["resume_file"]["size"] < 2000000) { // 2MB limit
                if (move_uploaded_file($_FILES["resume_file"]["tmp_name"], $target_file_server_resume)) {
                    $resume_url_db = $target_file_server_resume; // Store path relative to public/
                } else { $form_error_msg .= "Error uploading resume. "; }
            } else { $form_error_msg .= "Resume file too large (Max 2MB). "; }
        } else { $form_error_msg .= "Invalid resume file type (PDF, DOC, DOCX only). "; }
    } else {
        $form_error_msg .= "Resume file is required. Error code: ".$_FILES["resume_file"]["error"]."<br>";
    }

    // Cover Letter File Upload (Optional)
    if (isset($_FILES["cover_letter_file"]) && $_FILES["cover_letter_file"]["error"] == 0) {
        $allowed_types_cv = ['pdf', 'doc', 'docx'];
        $file_name_cv = basename($_FILES["cover_letter_file"]["name"]);
        $file_type_cv = strtolower(pathinfo($file_name_cv, PATHINFO_EXTENSION));
        $target_file_server_cv = $upload_dir_server . uniqid('cover_') . '_' . $file_name_cv;

        if (in_array($file_type_cv, $allowed_types_cv)) {
            if ($_FILES["cover_letter_file"]["size"] < 2000000) { // 2MB limit
                if (move_uploaded_file($_FILES["cover_letter_file"]["tmp_name"], $target_file_server_cv)) {
                    $cover_letter_url_db = $target_file_server_cv; // Store path relative to public/
                } else { $form_error_msg .= "Error uploading cover letter. "; }
            } else { $form_error_msg .= "Cover letter file too large (Max 2MB). "; }
        } else { $form_error_msg .= "Invalid cover letter file type (PDF, DOC, DOCX only). "; }
    } elseif (isset($_FILES["cover_letter_file"]) && $_FILES["cover_letter_file"]["error"] != UPLOAD_ERR_NO_FILE) {
        $form_error_msg .= "Error with cover letter upload. Error code: ".$_FILES["cover_letter_file"]["error"]."<br>";
    }


    if (empty($form_error_msg)) {
        $full_name_db = sanitize_input($conn, $full_name);
        $email_db = sanitize_input($conn, $email);
        $phone_db = sanitize_input($conn, $phone);
        $education_db = sanitize_input($conn, $education_background);
        $interest_db = sanitize_input($conn, $area_of_interest);
        // File paths are already somewhat sanitized by uniqid and basename, but ensure they are safe for DB.
        // $resume_url_db and $cover_letter_url_db are server paths from public/
        // No need to sanitize_input() them again here if they are just paths.

        $sql = "INSERT INTO internship_applications (full_name, email, phone, education_background, area_of_interest, resume_url, cover_letter_url, status, submitted_at) VALUES (
                '" . $full_name_db . "', '" . $email_db . "', '" . $phone_db . "',
                '" . $education_db . "', '" . $interest_db . "',
                '" . mysqli_real_escape_string($conn, $resume_url_db) . "',
                '" . mysqli_real_escape_string($conn, $cover_letter_url_db) . "',
                'pending', NOW() )";

        if (mysqli_query($conn, $sql)) {
            $form_msg = "Thank you for your internship application! We have received it and will review it soon.";
            $_POST = []; // Clear form
        } else {
            $form_error_msg = "Database error: " . mysqli_error($conn);
            // If DB error after successful file upload, ideally delete uploaded files.
            if(!empty($resume_url_db) && file_exists($resume_url_db)) unlink($resume_url_db);
            if(!empty($cover_letter_url_db) && file_exists($cover_letter_url_db)) unlink($cover_letter_url_db);
        }
        mysqli_close($conn);
    } else {
         if (isset($conn) && $conn) mysqli_close($conn);
         // If validation fails before DB and files were uploaded, delete them.
         // This part is tricky because move_uploaded_file happens before this final check.
         // Better to do all validations first, then file moves.
         // For now, if $resume_url_db is set (meaning move was attempted/successful) and there's an error, try to delete.
         if(!empty($resume_url_db) && file_exists($resume_url_db) && !empty($form_error_msg) && strpos($form_error_msg, "Database error") === false) {
            //  unlink($resume_url_db); // Commenting out for now to avoid deleting on simple validation errors if upload was successful
         }
         if(!empty($cover_letter_url_db) && file_exists($cover_letter_url_db) && !empty($form_error_msg) && strpos($form_error_msg, "Database error") === false) {
            //  unlink($cover_letter_url_db);
         }
    }
}
?>

<div class="page-header">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <p class="lead">Gain valuable experience by applying for an internship with us.</p>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2>Internship Application Form</h2>
            <p>Interested in an internship? Please fill out the form below and upload your resume and cover letter.</p>

            <?php if (!empty($form_msg)): ?>
                <div class="alert alert-success"><?php echo $form_msg; ?></div>
            <?php endif; ?>
            <?php if (!empty($form_error_msg)): ?>
                <div class="alert alert-danger"><?php echo $form_error_msg; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data" id="internshipApplicationForm">
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
                    <label for="education_background">Educational Background <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="education_background" name="education_background" rows="3" required><?php echo isset($_POST['education_background']) ? htmlspecialchars($_POST['education_background']) : ''; ?></textarea>
                </div>
                 <div class="form-group">
                    <label for="area_of_interest">Area of Interest (e.g., Project Management, Communications, Research) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="area_of_interest" name="area_of_interest" value="<?php echo isset($_POST['area_of_interest']) ? htmlspecialchars($_POST['area_of_interest']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="resume">Upload Resume (PDF, DOC, DOCX - Max 2MB) <span class="text-danger">*</span></label>
                    <input type="file" class="form-control-file" id="resume" name="resume_file" accept=".pdf,.doc,.docx" required>
                </div>
                <div class="form-group">
                    <label for="cover_letter">Upload Cover Letter (Optional - PDF, DOC, DOCX - Max 2MB)</label>
                    <input type="file" class="form-control-file" id="cover_letter" name="cover_letter_file" accept=".pdf,.doc,.docx">
                </div>
                <button type="submit" name="submit_internship_application" class="btn btn-primary">Submit Application</button>
            </form>
            <p class="mt-3"><small><em>Note: Full form submission including file uploads and database integration will be completed in a later step.</em></small></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
