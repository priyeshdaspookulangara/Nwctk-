</div> <!-- /.container-fluid mt-3 -->
    </div> <!-- /#content -->
</div> <!-- /.wrapper -->

<footer class="footer">
    <div class="container">
        <span>&copy; <?php echo date("Y"); ?> NGO Admin Panel. All Rights Reserved.</span>
    </div>
</footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Custom Script for Sidebar Toggle -->
<script type="text/javascript">
    $(document).ready(function () {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
            $(this).toggleClass('active'); // Optional: for styling the button itself
        });

        // Optional: Close sidebar when clicking outside on smaller screens
        // $(document).on('click', function (event) {
        //     if ($('#sidebar').hasClass('active') &&
        //         !$(event.target).closest('#sidebar, #sidebarCollapse').length) {
        //         if ($(window).width() < 768) { // Only for small screens
        //             $('#sidebar').removeClass('active');
        //             $('#sidebarCollapse').removeClass('active');
        //         }
        //     }
        // });

        // Active link highlighting based on current URL
        var currentUrl = window.location.pathname.split('/').pop();
        if (currentUrl === '') {
            currentUrl = 'dashboard.php'; // Default to dashboard if path is empty (e.g. /admin/)
        }
        $('#sidebar ul li a').each(function() {
            var link = $(this).attr('href');
            if (link === currentUrl) {
                $(this).closest('li').addClass('active');
                // If it's a sub-menu item, also open its parent dropdown
                if ($(this).closest('ul.collapse').length) {
                    $(this).closest('ul.collapse').addClass('show');
                    $(this).closest('ul.collapse').parent('li').find('a[data-toggle="collapse"]').attr('aria-expanded', 'true');
                }
            }
        });
    });
</script>

</body>
</html>
