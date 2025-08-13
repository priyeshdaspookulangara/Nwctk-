<?php
// auth_check.php is included in each main admin page, not directly in header.php
// to allow login.php and potentially other non-auth pages to not include it.
// However, if a page includes this header, it's implied it needs auth.
// For robustness, individual pages should include auth_check.php first.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Check if admin is logged in, redirect to login if not.
// This is a safeguard; primary auth check should be in the page itself.
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    // Determine path to login.php. Assumes header.php is in admin/includes/
    // and login.php is in admin/
    $path_to_login = "../login.php";

    // Check if the current script is login.php to prevent redirect loop
    if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
        header("location: " . $path_to_login);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Admin Panel - NGO Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Custom Admin CSS -->
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column; /* Make body a flex column for sticky footer */
        }
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            flex-grow: 1; /* Allows wrapper to grow and push footer down */
        }
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            background: #343a40; /* Dark sidebar */
            color: #fff;
            transition: all 0.3s;
        }
        #sidebar.active {
            margin-left: -250px;
        }
        #sidebar .sidebar-header {
            padding: 20px;
            background: #2c3136; /* Slightly darker header for sidebar */
            text-align: center;
        }
        #sidebar ul.components {
            padding: 20px 0;
            border-bottom: 1px solid #47748b;
        }
        #sidebar ul p {
            color: #fff;
            padding: 10px;
        }
        #sidebar ul li a {
            padding: 10px 20px;
            font-size: 1.1em;
            display: block;
            color: #adb5bd; /* Lighter text for links */
            text-decoration: none;
        }
        #sidebar ul li a:hover {
            color: #fff;
            background: #495057; /* Hover background for links */
        }
        #sidebar ul li.active > a, a[aria-expanded="true"] {
            color: #fff;
            background: #007bff; /* Active link background */
        }
        a[data-toggle="collapse"] {
            position: relative;
        }
        .dropdown-toggle::after {
            display: block;
            position: absolute;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
        }
        #content {
            width: 100%;
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s;
        }
        .navbar-custom {
            background-color: #f8f9fa; /* Light background for top navbar */
            border-bottom: 1px solid #dee2e6;
        }
        .footer {
            background-color: #f1f1f1;
            text-align: center;
            padding: 10px 0;
            font-size: 0.9em;
            color: #666;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar will be included here by sidebar.php -->
    <?php // include 'sidebar.php'; // Or included by the main page template ?>

    <!-- Page Content Holder -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-info">
                    <i class="fas fa-align-left"></i>
                    <span>Toggle Sidebar</span>
                </button>
                <div class="ml-auto">
                    <span class="navbar-text mr-3">
                        Welcome, <?php echo htmlspecialchars($_SESSION["admin_username"]); ?>!
                    </span>
                    <a href="logout.php" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </nav>

        <div class="container-fluid mt-3">
            <!-- Main content of each page will go here -->

<!-- The closing tags for body, html and the content div will be in footer.php -->
