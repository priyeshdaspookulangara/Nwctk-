<?php
// This script is for running migrations.
// It should be run from the command line.

// Include database configuration
require_once '../includes/db.php';

// The migration file to run
$migration_file = 'migrations/001_add_blog_tables.sql';

// Read the SQL file
$sql = file_get_contents($migration_file);
if ($sql === false) {
    die("Error: Could not read migration file: $migration_file\n");
}

// Execute the multi-query
if (mysqli_multi_query($conn, $sql)) {
    do {
        // Store first result set
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($conn));
    echo "Migration applied successfully: $migration_file\n";
} else {
    echo "Error applying migration: " . mysqli_error($conn) . "\n";
}

// Close the connection
mysqli_close($conn);
?>
