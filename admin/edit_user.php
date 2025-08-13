<?php
// Initialize the session
session_start();

// Include config file
require_once "../config.php";

// Check if the user is logged in and has the admin role
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../login.php");
    exit;
}

// Define variables
$user = null;
$user_id = 0;
$new_role = "";
$role_err = "";
$possible_roles = ['registered_user', 'member', 'intern', 'volunteer', 'admin'];

// Process user ID from URL
if(isset($_GET['id']) && !empty(trim($_GET['id']))){
    $user_id = trim($_GET['id']);
} else {
    header("location: index.php");
    exit;
}

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate role
    if(isset($_POST['role']) && in_array($_POST['role'], $possible_roles)){
        $new_role = $_POST['role'];
    } else {
        $role_err = "Please select a valid role.";
    }

    // If no errors, update the database
    if(empty($role_err)){
        $sql_update = "UPDATE users SET role = ? WHERE id = ?";
        if($stmt_update = mysqli_prepare($link, $sql_update)){
            mysqli_stmt_bind_param($stmt_update, "si", $new_role, $user_id);
            if(mysqli_stmt_execute($stmt_update)){
                header("location: index.php");
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt_update);
        }
    }
}

// Fetch user data for displaying in the form
$sql_fetch = "SELECT id, username, email, role FROM users WHERE id = ?";
if($stmt_fetch = mysqli_prepare($link, $sql_fetch)){
    mysqli_stmt_bind_param($stmt_fetch, "i", $user_id);
    if(mysqli_stmt_execute($stmt_fetch)){
        $result = mysqli_stmt_get_result($stmt_fetch);
        if(mysqli_num_rows($result) == 1){
            $user = mysqli_fetch_assoc($result);
        } else {
            // User not found
            header("location: index.php");
            exit();
        }
    }
    mysqli_stmt_close($stmt_fetch);
}

mysqli_close($link);

// Redirect if user object is not set
if(is_null($user)){
    header("location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Edit User</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="wrapper">
        <h2>Edit User: <?php echo htmlspecialchars($user['username']); ?></h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $user_id; ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>">
                    <?php foreach($possible_roles as $role): ?>
                        <option value="<?php echo $role; ?>" <?php echo ($user['role'] == $role) ? 'selected' : ''; ?>>
                            <?php echo ucfirst(str_replace('_', ' ', $role)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="invalid-feedback"><?php echo $role_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Save Changes">
                <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
