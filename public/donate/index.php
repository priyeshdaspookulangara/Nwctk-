<?php
$page_title = "Donations";
require_once '../includes/header.php';
require_once '../../includes/db.php'; // Path is different now, from public/donate/ to root includes/

$donations_content = "";
$page_name_db = 'donations';

// Fetch the content for the donations page
$sql = "SELECT content FROM page_content WHERE page_name = '" . sanitize_input($conn, $page_name_db) . "'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $donations_content = $row['content'];
} else {
    // Fallback content if nothing is found in the database
    $donations_content = '<div class="alert alert-warning">Content for this page is not available yet. Please check back later.</div>';
}
mysqli_close($conn);
?>

<div class="container mt-5">
    <?php echo htmlspecialchars_decode($donations_content); // Use htmlspecialchars_decode to render the HTML from DB ?>
</div>

<?php require_once '../includes/footer.php'; ?>
