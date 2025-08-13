<?php
// Initialize the session
session_start();

// Include config file
require_once "config.php";

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if post_id is set
if(isset($_GET['id']) && !empty(trim($_GET['id']))){
    $post_id = trim($_GET['id']);
    $user_id = $_SESSION['id'];

    // Check if the user has already liked the post
    $sql_check = "SELECT id FROM likes WHERE post_id = ? AND user_id = ?";

    if($stmt_check = mysqli_prepare($link, $sql_check)){
        mysqli_stmt_bind_param($stmt_check, "ii", $post_id, $user_id);

        if(mysqli_stmt_execute($stmt_check)){
            mysqli_stmt_store_result($stmt_check);

            if(mysqli_stmt_num_rows($stmt_check) == 1){
                // User has liked the post, so unlike it (DELETE)
                $sql_unlike = "DELETE FROM likes WHERE post_id = ? AND user_id = ?";
                if($stmt_unlike = mysqli_prepare($link, $sql_unlike)){
                    mysqli_stmt_bind_param($stmt_unlike, "ii", $post_id, $user_id);
                    mysqli_stmt_execute($stmt_unlike);
                    mysqli_stmt_close($stmt_unlike);
                }
            } else {
                // User has not liked the post, so like it (INSERT)
                $sql_like = "INSERT INTO likes (post_id, user_id) VALUES (?, ?)";
                if($stmt_like = mysqli_prepare($link, $sql_like)){
                    mysqli_stmt_bind_param($stmt_like, "ii", $post_id, $user_id);
                    mysqli_stmt_execute($stmt_like);
                    mysqli_stmt_close($stmt_like);
                }
            }
        }
        mysqli_stmt_close($stmt_check);
    }

    // Close connection
    mysqli_close($link);

    // Redirect back to the previous page
    if(isset($_SERVER['HTTP_REFERER'])){
        header("location: " . $_SERVER['HTTP_REFERER']);
    } else {
        // Fallback redirection if referer is not set
        header("location: index.php");
    }
    exit();

} else {
    // If post_id is not provided, redirect to index
    header("location: index.php");
    exit();
}
?>
