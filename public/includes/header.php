<?php
// Define a base URL if not already defined (useful for absolute paths in links/css/js)
if (!defined('BASE_URL')) {
    // Auto-detect scheme, host. For subdirectories, adjust accordingly.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    // If your app is in a subdirectory, e.g., http://localhost/my_ngo_app/public/
    // you might need to manually set it or have more complex logic.
    // For simplicity, assuming it's at the root or /public/ is the web root.
    // This basic detection might need adjustment based on actual deployment.
    // A common practice is to define this in a central config file.

    // Simplistic approach: Get path to the 'public' directory
    $script_name = $_SERVER['SCRIPT_NAME']; // e.g., /index.php or /subdir/index.php
    // If public is part of the URL path, like /my_app/public/index.php
    if (strpos($script_name, '/public/') !== false) {
        $path_to_public = substr($script_name, 0, strpos($script_name, '/public/') + strlen('/public/'));
    } else {
        // If public is the document root, path is just /
        // Or if script is like /index.php (meaning public is likely doc root)
        $path_to_public = '/';
        // This needs to be accurate. If public is /var/www/html/public and web root is /var/www/html,
        // then BASE_URL should be /public/
        // If web root is /var/www/html/public, then BASE_URL should be /
    }
    // A more reliable way if this header.php is always in public/includes/
    // and index.php is in public/
    $base_path = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    // Remove 'includes/' if present to get to public directory root
    $base_url_path = preg_replace('/includes\/$/', '', $base_path);
    // This is still relative from web root.
    // For true robustness, this should be a configured value.
    // Let's assume public is the web root for now for links like /css/style.css
    define('BASE_URL', '/'); // Adjust if your public folder is not the web root (e.g. /my_app/public/)
}

// Determine the current page to set active class in navbar
$current_page = basename($_SERVER['PHP_SELF']);
if (basename(dirname($_SERVER['PHP_SELF'])) === 'contact' && $current_page === 'index.php') {
    $current_page = 'contact'; // Special case for contact/index.php
}

// Database connection (optional here, depends if header needs dynamic data)
// require_once dirname(__FILE__) . '/../../includes/db.php'; // Path from public/includes to project_root/includes
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - NGO Name' : 'NGO Name - Welcome'; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Custom CSS -->
    <!-- The path needs to be relative to the including file OR an absolute path from web root -->
    <?php
        // Calculate path to css/style.css from the current script's directory
        // This is tricky. A defined BASE_URL is better.
        // Assuming this header is in public/includes/ and css is in public/css/
        // For a file in public/ (e.g. index.php), path is "css/style.css"
        // For a file in public/contact/ (e.g. contact/index.php), path is "../css/style.css"

        $path_to_css = "css/style.css"; // Default if in public/
        $path_to_base_url_for_assets = "";

        // Determine depth for asset paths
        $path_from_public_root = trim(str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']), '/');
        $depth = substr_count($path_from_public_root, '/');
        if (basename(dirname($_SERVER['PHP_SELF'])) === 'public' || dirname($_SERVER['PHP_SELF']) === '/' || dirname($_SERVER['PHP_SELF']) === '\\' ) { // If script is directly in public
             $path_to_base_url_for_assets = '';
        } else if (basename(dirname(dirname($_SERVER['PHP_SELF']))) === 'public' || dirname(dirname($_SERVER['PHP_SELF'])) === '/' || dirname(dirname($_SERVER['PHP_SELF'])) === '\\') { // If script is one level deep from public (e.g. public/contact/index.php)
             $path_to_base_url_for_assets = '../';
        } else { // Deeper or unknown structure, fallback or more complex logic needed
            // For now, assume max one level deep or directly in public
            // This part is the most fragile without a fixed BASE_URL for assets.
            // Let's try to make it more robust based on 'public' folder in path.
            $doc_root = $_SERVER['DOCUMENT_ROOT'];
            $script_filename = $_SERVER['SCRIPT_FILENAME'];
            $relative_script_path = str_replace($doc_root, '', $script_filename); // Path from web root

            $path_parts = explode('/', dirname($relative_script_path));
            $public_found_at = -1;
            foreach($path_parts as $i => $part) {
                if (strtolower($part) === 'public') {
                    $public_found_at = $i;
                    break;
                }
            }

            if ($public_found_at !== -1) {
                $levels_deep_in_public = count($path_parts) - 1 - $public_found_at;
                $path_to_base_url_for_assets = str_repeat('../', $levels_deep_in_public);
            } else {
                 // If 'public' is not in the path (e.g., public is the doc root)
                 $levels_deep_in_public = count(array_filter(explode('/', dirname($relative_script_path))));
                 $path_to_base_url_for_assets = str_repeat('../', $levels_deep_in_public);
            }
        }
        // This logic for $path_to_base_url_for_assets is complex due to unknown deployment structure.
        // A simpler, common way is to use absolute paths from the web root if BASE_URL is set correctly.
        // Example: <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
        // For now, using a simplified relative path logic based on common structures.

        // Let's assume `BASE_URL` is set to the root of the public directory (e.g., "/" or "/app/public/")
        // And this header file is included from files within that public directory or its subdirectories.
    ?>
    <link rel="stylesheet" href="<?php echo rtrim($path_to_base_url_for_assets, '/'); ?>/css/style.css">

    <style>
        .navbar-brand-logo { max-height: 40px; margin-right: 10px; }
        body { padding-top: 56px; /* Adjust if navbar height changes */ }
        .main-content { padding: 20px 0; }
        .footer { background-color: #f8f9fa; padding: 20px 0; margin-top: 30px; border-top: 1px solid #e7e7e7; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $path_to_base_url_for_assets; ?>index.php">
            <!-- <img src="<?php echo $path_to_base_url_for_assets; ?>images/logo.png" alt="NGO Logo" class="navbar-brand-logo"> -->
            NGO Name
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo $path_to_base_url_for_assets; ?>index.php">Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo $path_to_base_url_for_assets; ?>about.php">About Us</a>
                </li>
                <li class="nav-item <?php echo ($current_page == 'news.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo $path_to_base_url_for_assets; ?>news.php">News & Events</a>
                </li>
                <li class="nav-item <?php echo ($current_page == 'projects.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo $path_to_base_url_for_assets; ?>projects.php">Projects</a>
                </li>
                <li class="nav-item <?php echo ($current_page == 'camps.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo $path_to_base_url_for_assets; ?>camps.php">Camps</a>
                </li>
                <li class="nav-item dropdown <?php echo in_array($current_page, ['gallery.php', 'videos.php']) ? 'active' : ''; ?>">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarMediaDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Media
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarMediaDropdown">
                        <a class="dropdown-item <?php echo ($current_page == 'gallery.php') ? 'active' : ''; ?>" href="<?php echo $path_to_base_url_for_assets; ?>gallery.php">Photo Gallery</a>
                        <a class="dropdown-item <?php echo ($current_page == 'videos.php') ? 'active' : ''; ?>" href="<?php echo $path_to_base_url_for_assets; ?>videos.php">Videos</a>
                    </div>
                </li>
                 <li class="nav-item <?php echo ($current_page == 'testimonials.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo $path_to_base_url_for_assets; ?>testimonials.php">Testimonials</a>
                </li>
                <li class="nav-item dropdown <?php echo in_array($current_page, ['join_member.php', 'join_volunteer.php', 'apply_internship.php']) ? 'active' : ''; ?>">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarJoinUsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Join Us
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarJoinUsDropdown">
                        <a class="dropdown-item <?php echo ($current_page == 'join_member.php') ? 'active' : ''; ?>" href="<?php echo $path_to_base_url_for_assets; ?>join_member.php">Become a Member</a>
                        <a class="dropdown-item <?php echo ($current_page == 'join_volunteer.php') ? 'active' : ''; ?>" href="<?php echo $path_to_base_url_for_assets; ?>join_volunteer.php">Become a Volunteer</a>
                        <a class="dropdown-item <?php echo ($current_page == 'apply_internship.php') ? 'active' : ''; ?>" href="<?php echo $path_to_base_url_for_assets; ?>apply_internship.php">Apply for Internship</a>
                    </div>
                </li>
                <li class="nav-item <?php echo ($current_page == 'contact') ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo $path_to_base_url_for_assets; ?>contact/">Contact Us</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main role="main" class="container main-content">
    <!-- Page specific content starts here -->
