<!-- Sidebar -->
<nav id="sidebar">
    <div class="sidebar-header">
        <h3>NGO Admin</h3>
        <strong>NA</strong> <!-- Short for NGO Admin when collapsed, if we implement collapse to icons only -->
    </div>

    <ul class="list-unstyled components">
        <li>
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
        </li>
        <li>
            <a href="#bannerManagementSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-images"></i>
                Banners
            </a>
            <ul class="collapse list-unstyled" id="bannerManagementSubmenu">
                <li><a href="manage_banners.php">Manage Banners</a></li>
                <li><a href="add_banner.php">Add Banner</a></li>
            </ul>
        </li>
        <li>
            <a href="#contentManagementSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-file-alt"></i>
                Page Content
            </a>
            <ul class="collapse list-unstyled" id="contentManagementSubmenu">
                <li><a href="edit_home_content.php">Home Page</a></li>
                <li><a href="edit_about_content.php">About Us Page</a></li>
                <li><a href="edit_contact_content.php">Contact Page</a></li>
            </ul>
        </li>
        <li>
            <a href="#trusteeSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-users-cog"></i>
                Trustees
            </a>
            <ul class="collapse list-unstyled" id="trusteeSubmenu">
                <li><a href="manage_trustees.php">Manage Trustees</a></li>
                <li><a href="add_trustee.php">Add Trustee</a></li>
            </ul>
        </li>
        <li>
            <a href="#newsEventsSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-newspaper"></i>
                News & Events
            </a>
            <ul class="collapse list-unstyled" id="newsEventsSubmenu">
                <li><a href="manage_news.php">Manage News/Events</a></li>
                <li><a href="add_news.php">Add News/Event</a></li>
            </ul>
        </li>
        <li>
            <a href="#blogSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-blog"></i>
                Blog
            </a>
            <ul class="collapse list-unstyled" id="blogSubmenu">
                <li><a href="manage_blog_posts.php">Manage Posts</a></li>
                <li><a href="add_blog_post.php">Add Post</a></li>
                <li><a href="manage_blog_categories.php">Manage Categories</a></li>
            </ul>
        </li>
        <li>
            <a href="#projectsSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-project-diagram"></i>
                Projects
            </a>
            <ul class="collapse list-unstyled" id="projectsSubmenu">
                <li><a href="manage_projects.php">Manage Projects</a></li>
                <li><a href="add_project.php">Add Project</a></li>
            </ul>
        </li>
        <li>
            <a href="#campsSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-campground"></i>
                Camps
            </a>
            <ul class="collapse list-unstyled" id="campsSubmenu">
                <li><a href="manage_camps.php">Manage Camps</a></li>
                <li><a href="add_camp.php">Add Camp</a></li>
            </ul>
        </li>
        <li>
            <a href="#mediaSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-photo-video"></i>
                Media
            </a>
            <ul class="collapse list-unstyled" id="mediaSubmenu">
                <li><a href="manage_photos.php">Manage Photos</a></li>
                <li><a href="add_photo.php">Add Photo</a></li>
                <li><a href="manage_videos.php">Manage Videos</a></li>
                <li><a href="add_video.php">Add Video</a></li>
            </ul>
        </li>
        <li>
            <a href="#testimonialsSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-comments"></i>
                Testimonials
            </a>
            <ul class="collapse list-unstyled" id="testimonialsSubmenu">
                <li><a href="manage_testimonials.php">Manage Testimonials</a></li>
                <li><a href="add_testimonial.php">Add Testimonial</a></li>
            </ul>
        </li>
        <li>
            <a href="#requestsSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-paper-plane"></i>
                Requests & Forms
            </a>
            <ul class="collapse list-unstyled" id="requestsSubmenu">
                <li><a href="manage_member_requests.php">Member Requests</a></li>
                <li><a href="manage_volunteer_requests.php">Volunteer Requests</a></li>
                <li><a href="manage_internship_applications.php">Internship Applications</a></li>
                <li><a href="manage_inquiries.php">Inquiries</a></li>
            </ul>
        </li>
        <!-- Add more navigation items as needed -->
    </ul>

    <ul class="list-unstyled CTAs">
        <li>
            <a href="../public/index.php" class="download" target="_blank">
                <i class="fas fa-external-link-alt"></i> View Public Site
            </a>
        </li>
        <li>
            <a href="logout.php" class="article">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</nav>

<style>
/* Additional styles for CTAs if needed */
#sidebar ul.CTAs {
    padding: 20px;
}
#sidebar ul.CTAs a {
    text-align: center;
    font-size: 0.9em !important;
    display: block;
    border-radius: 5px;
    margin-bottom: 5px;
    color: #fff; /* White text for buttons */
}
#sidebar ul.CTAs a.download {
    background: #007bff; /* Bootstrap primary blue */
    color: #fff;
}
#sidebar ul.CTAs a.download:hover {
    background: #0056b3; /* Darker blue on hover */
}
#sidebar ul.CTAs a.article {
    background: #dc3545; /* Bootstrap danger red */
    color: #fff;
}
#sidebar ul.CTAs a.article:hover {
    background: #b02a37; /* Darker red on hover */
}

/* Ensure icons are spaced nicely */
#sidebar ul li a i {
    margin-right: 10px;
}
</style>
