<!-- Page specific content ends here -->
</main><!-- /.container -->

<footer class="footer">
    <div class="container text-center">
        <p class="mb-1">&copy; <?php echo date("Y"); ?> NGO Name. All Rights Reserved.</p>
        <ul class="list-inline">
            <li class="list-inline-item"><a href="#">Privacy Policy</a></li>
            <li class="list-inline-item"><a href="#">Terms of Use</a></li>
            <li class="list-inline-item"><a href="<?php
                // Path calculation for admin link
                $path_to_admin_link = '';
                $current_script_depth_from_public_root = 0;

                // Calculate depth from 'public' directory
                $script_path = dirname($_SERVER['SCRIPT_NAME']); // e.g. /myapp/public or /myapp/public/contact
                $public_pos = strpos($script_path, '/public/');

                if ($public_pos !== false) {
                    $path_after_public = substr($script_path, $public_pos + strlen('/public/'));
                    if (!empty($path_after_public)) {
                        $current_script_depth_from_public_root = count(array_filter(explode('/', $path_after_public)));
                    }
                } else {
                    // Fallback if '/public/' is not in the path (e.g. public is the web root)
                    $current_script_depth_from_public_root = count(array_filter(explode('/', ltrim($script_path, '/'))));
                }

                $path_to_admin_link = str_repeat('../', $current_script_depth_from_public_root + 1) . 'admin/login.php';
                // The +1 is because admin is sibling to public, so from inside public, need one '../' to get out.
                // If script is in public/index.php (depth 0), path is ../admin/login.php
                // If script is in public/contact/index.php (depth 1), path is ../../admin/login.php

                // A simpler approach if BASE_URL for public is known:
                // echo rtrim(BASE_URL, '/') . '/../admin/login.php'; // This assumes BASE_URL points to /public
                // Or define an ADMIN_URL constant.
                // For now, the dynamic calculation:
                echo htmlspecialchars($path_to_admin_link);
            ?>">Admin Login</a></li>
        </ul>
    </div>
</footer>

<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Custom JS -->
<?php
    // Path calculation for JS file (similar to CSS in header)
    $path_to_js_script = '';
    // Using the $path_to_base_url_for_assets logic from header.php would be ideal if passed or recalculated
    // For simplicity here, assuming similar logic or a correctly defined BASE_URL for assets.
    // Let's reuse the depth calculation concept from header for consistency (if available)
    // Or recalculate:
    $path_to_base_url_for_assets_footer_js = "";
    $doc_root_js = $_SERVER['DOCUMENT_ROOT'];
    $script_filename_js = $_SERVER['SCRIPT_FILENAME'];
    $relative_script_path_js = str_replace($doc_root_js, '', $script_filename_js);

    $path_parts_js = explode('/', dirname($relative_script_path_js));
    $public_found_at_js = -1;
    foreach($path_parts_js as $i_js => $part_js) {
        if (strtolower($part_js) === 'public') {
            $public_found_at_js = $i_js;
            break;
        }
    }

    if ($public_found_at_js !== -1) {
        $levels_deep_in_public_js = count($path_parts_js) - 1 - $public_found_at_js;
        $path_to_base_url_for_assets_footer_js = str_repeat('../', $levels_deep_in_public_js);
    } else {
         $levels_deep_in_public_js = count(array_filter(explode('/', dirname($relative_script_path_js))));
         $path_to_base_url_for_assets_footer_js = str_repeat('../', $levels_deep_in_public_js);
    }
?>
<script src="<?php echo rtrim($path_to_base_url_for_assets_footer_js, '/'); ?>/js/script.js"></script>

</body>
</html>
