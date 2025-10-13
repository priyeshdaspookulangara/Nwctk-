<?php
$page_title = "Register";
require_once 'includes/header.php';
require_once '../includes/db.php';

$username = "";
$email = "";
$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
        $error_msg = "All fields are required.";
    } elseif ($password !== $password_confirm) {
        $error_msg = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error_msg = "Password must be at least 8 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Invalid email format.";
    } else {
        // Check if username or email already exists
        $sql_check = "SELECT id FROM users WHERE username = ? OR email = ?";
        if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
            mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $error_msg = "Username or email already taken.";
            }
            mysqli_stmt_close($stmt_check);
        }

        // If no errors, proceed with registration
        if (empty($error_msg)) {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);

            $sql_insert = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            if ($stmt_insert = mysqli_prepare($conn, $sql_insert)) {
                mysqli_stmt_bind_param($stmt_insert, "sss", $username, $email, $password_hashed);

                if (mysqli_stmt_execute($stmt_insert)) {
                    $success_msg = "Registration successful! You can now <a href='login.php'>login</a>.";
                    // Clear form fields
                    $username = "";
                    $email = "";
                } else {
                    $error_msg = "Something went wrong. Please try again later.";
                }
                mysqli_stmt_close($stmt_insert);
            }
        }
    }
    mysqli_close($conn);
}
?>

<div class="page-header">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header">
                    Create a New Account
                </div>
                <div class="card-body">
                    <?php if (!empty($error_msg)): ?>
                        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success_msg)): ?>
                        <div class="alert alert-success"><?php echo $success_msg; ?></div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="password_confirm">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Register</button>
                    </form>
                    <div class="mt-3 text-center">
                        Already have an account? <a href="login.php">Login here</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
