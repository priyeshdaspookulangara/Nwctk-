<?php
session_start();
require_once '../includes/db.php'; // Database connection

// If already logged in, redirect to dashboard
if(isset($_SESSION["admin_loggedin"]) && $_SESSION["admin_loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

$username = $password = "";
$login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    if(empty(trim($_POST["username"]))){
        $login_err = "Please enter username.";
    } else {
        // Sanitize username - though not strictly needed for comparison if not directly in SQL, good practice
        $username = sanitize_input($conn, trim($_POST["username"]));
    }

    if(empty(trim($_POST["password"]))){
        $login_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]); // Password comparison will be direct
    }

    if(empty($login_err)){
        // Prepare a select statement
        // IMPORTANT: No bind_param or prepare as per requirements. Direct query construction.
        // Sanitize username again specifically for SQL query construction
        $username_sql = mysqli_real_escape_string($conn, $username);
        $sql = "SELECT id, username, password FROM admin_users WHERE username = '" . $username_sql . "'";

        $result = mysqli_query($conn, $sql);

        if($result){
            if(mysqli_num_rows($result) == 1){
                $row = mysqli_fetch_assoc($result);
                // IMPORTANT: Plain text password comparison.
                // In a real application, use password_verify($password, $row['password'])
                if($password === $row['password']){ // Direct comparison
                    // Password is correct, so start a new session
                    session_start(); // Ensure session is started

                    // Store data in session variables
                    $_SESSION["admin_loggedin"] = true;
                    $_SESSION["admin_id"] = $row['id'];
                    $_SESSION["admin_username"] = $row['username'];

                    // Redirect user to dashboard page
                    header("location: dashboard.php");
                    exit;
                } else {
                    // Password is not valid
                    $login_err = "Invalid username or password.";
                }
            } else {
                // Username doesn't exist
                $login_err = "Invalid username or password.";
            }
        } else {
            $login_err = "Oops! Something went wrong. Please try again later. " . mysqli_error($conn);
        }
        mysqli_free_result($result);
    }
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .login-form {
            width: 100%;
            max-width: 330px;
            padding: 15px;
            margin: auto;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h2 class="text-center mb-4">Admin Login</h2>
        <?php
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary btn-block" value="Login">
            </div>
        </form>
    </div>

    <!-- jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
