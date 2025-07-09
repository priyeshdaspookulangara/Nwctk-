// Custom Frontend JavaScript for NGO Website

$(document).ready(function(){

    // Initialize Ekko Lightbox
    // Ensure Ekko Lightbox JS is loaded before this script if this is the only place it's initialized.
    // It was also added in gallery.php, so this acts as a consolidated point.
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
       event.preventDefault();
       $(this).ekkoLightbox({
           alwaysShowClose: true
           // Other options:
           // rightArrow: '<i class="fas fa-chevron-right"></i>',
           // leftArrow: '<i class="fas fa-chevron-left"></i>'
       });
    });

    // Back to Top Button Logic
    // First, add the button to the body if it's not there (e.g. if footer doesn't include it)
    if ($('#backToTopBtn').length === 0) {
        $('body').append('<button onclick="scrollToTop()" id="backToTopBtn" title="Go to top"><i class="fas fa-arrow-up"></i></button>');
    }

    var backToTopButton = $("#backToTopBtn");

    $(window).scroll(function() {
        if ($(window).scrollTop() > 300) { // Show button after scrolling 300px
            backToTopButton.fadeIn();
        } else {
            backToTopButton.fadeOut();
        }
    });

    // This function will be called when the button is clicked (defined globally for the inline onclick)
    // However, it's better to attach event listener with jQuery:
    backToTopButton.click(function() {
        $('html, body').animate({scrollTop: 0}, 800); // Smooth scroll to top
        return false; // Prevent default behavior
    });


    // Activate Bootstrap tooltips (if used anywhere)
    $('[data-toggle="tooltip"]').tooltip();

    // Optional: Add 'active' class to navbar links based on current page
    // This is already handled by PHP in header.php, but could be a JS fallback/enhancement.
    // var currentPageUrl = window.location.href;
    // $('.navbar-nav a.nav-link').each(function() {
    //     if (this.href === currentPageUrl) {
    //         $(this).closest('.nav-item').addClass('active');
    //     }
    // });

    console.log("Custom script.js loaded and initialized.");
});

// Make scrollToTop globally accessible if using inline onclick (though jQuery click is better)
// function scrollToTop() {
//     $('html, body').animate({scrollTop: 0}, 800);
// }
