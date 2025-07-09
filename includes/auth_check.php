<?php
// Initialize the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    // Determine the correct path to login.php based on the current script's location
    // This assumes auth_check.php is in 'includes/' and admin pages are in 'admin/'
    // If an admin script is deeper, this might need adjustment or a base URL config
    $login_path = "login.php"; // Default if current script is in admin/

    // A more robust way could be to define a base admin URL
    // For now, this simple check works if auth_check is included from files directly in admin/
    // If included from admin/some_folder/file.php, it would need ../login.php
    // Let's assume for now it's included from files directly under admin/
    // Or, we can use an absolute path if a base URL is defined.
    // For simplicity, we'll redirect to 'login.php' assuming it's in the same directory
    // or that the web server handles the relative path correctly from the admin directory.
    // A better approach for multi-level admin directories would be:
    // header("location: " . ADMIN_BASE_URL . "login.php");
    // But since ADMIN_BASE_URL is not set, we'll use a relative path.
    // If this file is included from `admin/some_module/page.php`, it should be `../login.php`
    // If it's from `admin/page.php`, it should be `login.php`

    // A simple way to try and make it relative to the admin root.
    // This is a bit of a heuristic.
    if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
        // Count depth from admin base
        $admin_base_depth = substr_count(substr($_SERVER['PHP_SELF'], strpos($_SERVER['PHP_SELF'], '/admin/') + strlen('/admin/')), '/');
        $login_path = str_repeat('../', $admin_base_depth) . 'login.php';
         if ($admin_base_depth == 0 && basename($_SERVER['PHP_SELF']) == 'login.php') {
            // We are on login.php itself, do nothing.
        } else if ($admin_base_depth == 0) {
            $login_path = 'login.php';
        }
    } else {
        // Fallback, might not be correct if structure changes
        $login_path = 'login.php';
    }
    // A simpler, more common approach is to just use a fixed relative path from where it's expected to be included
    // e.g., if all protected files are in admin/, and login.php is in admin/, then "login.php" is fine.
    // If protected files are in admin/modules/, then "../login.php" from those files.
    // Given this auth_check.php is in includes/, and will be called from admin/
    // The path from admin/file.php to admin/login.php is just "login.php"
    // If called from admin/module/file.php, it would be "../login.php"

    // Let's refine this to be more robust by assuming the file including this
    // is within the /admin/ directory or a subdirectory of /admin/
    // And login.php is at the root of /admin/

    // Calculate path to admin root from current script
    $path_to_admin_root = '';
    $current_script_path = dirname($_SERVER['SCRIPT_NAME']); // Gets the directory of the script *including* this file
    $admin_folder_name = 'admin'; // Name of your admin directory

    // Check if current script is inside the admin directory
    if (strpos($current_script_path, '/' . $admin_folder_name . '/') !== false) {
        $segments = explode('/', trim($current_script_path, '/'));
        $path_parts = [];
        $found_admin = false;
        foreach ($segments as $segment) {
            if ($found_admin) {
                $path_parts[] = '..';
            }
            if ($segment == $admin_folder_name) {
                $found_admin = true;
            }
        }
        $path_to_admin_root = empty($path_parts) ? '' : implode('/', $path_parts) . '/';
         // if $path_to_admin_root is empty, it means we are in a script directly under /admin
         // if $path_to_admin_root is '../', it means we are in /admin/subdir/
         // if $path_to_admin_root is '../../', it means we are in /admin/subdir/subsubdir/
        $login_redirect_url = $path_to_admin_root . 'login.php';
         if (basename($_SERVER['PHP_SELF']) === 'login.php') {
             // Do not redirect if we are already on login.php
        } else {
             header("location: " . $login_redirect_url);
             exit;
        }

    } else if (basename(dirname($_SERVER['SCRIPT_NAME'])) === $admin_folder_name && basename($_SERVER['PHP_SELF']) !== 'login.php') {
        // If the script is directly inside the /admin folder (e.g. /admin/dashboard.php)
        header("location: login.php");
        exit;
    } else if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
        // Fallback for other cases, might need adjustment for complex structures
        // This assumes login.php is accessible from the current path.
        // This part is tricky without a defined base URL.
        // For a structure where auth_check is in /includes and used by /admin/file.php,
        // and login.php is /admin/login.php, the path from /admin/file.php to /admin/login.php is just "login.php"
        // The location header is relative to the current request URI's directory.
        // So if we are in /admin/dashboard.php, "location: login.php" is correct.
        // If we are in /admin/feature/manage.php, "location: ../login.php" would be correct.
        // The SCRIPT_NAME logic above should handle this.
        // A simple, often effective way if all admin files are in admin/ or subfolders of admin/
        // is to find the relative path to admin/login.php

        // Let's simplify. Assume auth_check.php is in `ROOT/includes/`
        // Admin pages are in `ROOT/admin/` or `ROOT/admin/subfolder/`
        // `login.php` is in `ROOT/admin/login.php`

        // Path from the script that *includes* this file to `admin/login.php`
        $current_dir = dirname($_SERVER['SCRIPT_FILENAME']); // e.g. /var/www/html/admin or /var/www/html/admin/module
        $admin_root = $_SERVER['DOCUMENT_ROOT']; // Need to find actual admin root relative to doc root

        // Find how many levels deep from 'admin' folder the current script is
        $script_path_from_doc_root = $_SERVER['SCRIPT_NAME']; // e.g., /myapp/admin/dashboard.php or /myapp/admin/module/page.php
        $admin_dir_identifier = '/admin/';
        $admin_pos = strpos($script_path_from_doc_root, $admin_dir_identifier);

        if ($admin_pos !== false && basename($script_path_from_doc_root) !== 'login.php') {
            $path_within_admin = substr($script_path_from_doc_root, $admin_pos + strlen($admin_dir_identifier));
            $depth = substr_count($path_within_admin, '/');
            $relative_login_path = str_repeat('../', $depth) . 'login.php';
            header("location: " . $relative_login_path);
            exit;
        } else if (basename($script_path_from_doc_root) !== 'login.php') {
            // Fallback if not in admin directory or structure is unexpected
            // This will likely fail if not called from within the admin directory context.
            // Consider defining a global constant for the admin path.
            header("location: login.php"); // This assumes it's called from a file in the admin root.
            exit;
        }
    }
}
?>
