<?php
// Database configuration
define('DB_SERVER', 'localhost'); // Replace with your database server
define('DB_USERNAME', 'root');    // Replace with your database username
define('DB_PASSWORD', '');        // Replace with your database password
define('DB_NAME', 'ngo_website_db'); // Replace with your database name

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    // Log error to a file or use a more robust error handling mechanism for production
    error_log("ERROR: Could not connect to database. " . mysqli_connect_error());
    // Display a user-friendly message (optional, could be handled by calling script)
    die("Error: Unable to connect to the database. Please try again later.");
}

// Set character set to utf8mb4 (recommended for full Unicode support)
if (!mysqli_set_charset($conn, "utf8mb4")) {
    error_log("Error loading character set utf8mb4: %s\n" . mysqli_error($conn));
}

// Function to sanitize input (as per requirements)
// This function should be called before using any variable in a SQL query
function sanitize_input($conn, $input) {
    if (is_array($input)) {
        foreach($input as $key => $value) {
            $input[$key] = sanitize_input($conn, $value);
        }
    } else {
        $input = trim($input);
        $input = stripslashes($input);
        // $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8'); // Consider if outputting to HTML directly
        $input = mysqli_real_escape_string($conn, $input);
    }
    return $input;
}

// Example of how to use sanitize_input:
// $safe_variable = sanitize_input($conn, $_POST['user_input']);

// The $conn variable will be used by other PHP scripts to interact with the database.
// No need to close the connection here, it will be closed automatically when the script ends,
// or can be closed explicitly by the calling script if needed (mysqli_close($conn)).
?>
