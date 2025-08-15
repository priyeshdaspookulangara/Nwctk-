<?php
$page_title = "Donations";

// Define the project root directory for robust includes
define('PROJECT_ROOT', dirname(__DIR__, 2));

require_once '../includes/header.php';
require_once PROJECT_ROOT . '/includes/db.php';

$donations_content = "";
$page_name_db = 'donations';

// Fetch the content for the donations page
$sql = "SELECT content FROM page_content WHERE page_name = '" . sanitize_input($conn, $page_name_db) . "'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $donations_content = $row['content'];
} else {
    // Fallback content if nothing is found in the database.
    // This could also be because the admin page hasn't been visited yet to create the initial content.
    $donations_content = '<div class="alert alert-warning">The content for this page is managed by the admin panel, but it has not been set up yet. Please check back later.</div>';
}
mysqli_close($conn);
?>

<div class="container mt-5">
    <?php echo htmlspecialchars_decode($donations_content); // Use htmlspecialchars_decode to render the HTML from DB ?>
</div>

<?php require_once '../includes/footer.php'; ?>
