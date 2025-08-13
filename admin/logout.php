<?php
// Initialize the session
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
if (session_destroy()) {
    // If session is destroyed successfully, redirect to login page
    header("location: login.php");
    exit;
} else {
    // If there was an issue destroying session, output an error
    // This is unlikely but good for completeness
    echo "Error: Could not log out. Please try again.";
    // Optionally, you could still try to redirect or provide a link
    echo '<br><a href="login.php">Go to Login</a>';
    exit;
}
?>
