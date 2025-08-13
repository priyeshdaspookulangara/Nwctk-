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

// Attempt to select all users
$users = [];
$sql = "SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC";
if($result = mysqli_query($link, $sql)){
    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            $users[] = $row;
        }
        mysqli_free_result($result);
    }
} else {
    echo "Oops! Something went wrong. Please try again later.";
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Users</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .wrapper { width: 90%; max-width: 1000px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="page-header">
            <h1>User Management</h1>
            <div>
                <a href="manage_posts.php" class="btn btn-primary">Manage Posts</a>
                <a href="../index.php" class="btn btn-secondary ml-2">Main Site</a>
                <a href="../logout.php" class="btn btn-danger ml-2">Sign Out</a>
            </div>
        </div>

        <?php if(empty($users)): ?>
            <p>No users found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo date("F j, Y", strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-secondary">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
