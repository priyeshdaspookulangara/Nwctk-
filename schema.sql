CREATE TABLE `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL, -- Store hashed passwords in a real application
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `trustees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `position` VARCHAR(255),
  `image_url` VARCHAR(255), -- Path to image
  `bio` TEXT,
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `news_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `image_url` VARCHAR(255), -- Path to image for the news/event
  `date` DATE,
  `type` ENUM('news', 'event') DEFAULT 'news',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `start_date` DATE,
  `end_date` DATE,
  `status` VARCHAR(100), -- e.g., Ongoing, Completed
  `image_url` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `camps` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `location` VARCHAR(255),
  `start_date` DATE,
  `end_date` DATE,
  `image_url` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `photos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255),
  `description` TEXT,
  `image_url` VARCHAR(255) NOT NULL, -- Path to image file
  `gallery_tag` VARCHAR(100), -- Optional: to group photos into galleries
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `videos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `video_url` VARCHAR(255) NOT NULL, -- Could be YouTube link or path to video file
  `video_type` ENUM('youtube', 'file') DEFAULT 'youtube',
  `thumbnail_url` VARCHAR(255), -- Optional: for file-based videos
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `testimonials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `author_name` VARCHAR(255) NOT NULL,
  `author_position` VARCHAR(255), -- e.g., Volunteer, Beneficiary
  `testimonial_text` TEXT NOT NULL,
  `image_url` VARCHAR(255), -- Optional: photo of the author
  `rating` INT CHECK (rating >= 1 AND rating <= 5), -- Optional: star rating
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `member_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50),
  `address` TEXT,
  `reason_to_join` TEXT,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `volunteer_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50),
  `availability` VARCHAR(255), -- e.g., Weekends, Specific dates
  `skills_interests` TEXT,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `internship_applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50),
  `education_background` TEXT,
  `area_of_interest` VARCHAR(255),
  `cover_letter_url` VARCHAR(255), -- Path to uploaded CV/cover letter
  `resume_url` VARCHAR(255), -- Path to uploaded CV/cover letter
  `status` ENUM('pending', 'under_review', 'accepted', 'rejected') DEFAULT 'pending',
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `inquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50),
  `subject` VARCHAR(255),
  `message` TEXT NOT NULL,
  `status` ENUM('new', 'read', 'replied') DEFAULT 'new',
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `page_content` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `page_name` VARCHAR(100) NOT NULL UNIQUE, -- e.g., 'home', 'about_us'
  `content` LONGTEXT,
  `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default admin user (password: admin) - HASH THIS IN A REAL APP
INSERT INTO `admin_users` (`username`, `password`) VALUES ('admin', 'admin');
-- For a real application, use password_hash() in PHP:
-- e.g., password_hash('admin', PASSWORD_DEFAULT) which might produce something like '$2y$10$N0gO9A4G5fH6I7J8K9L0MuWXnOflPUjZzY1N6JgQzU0pH0sL0pGsq'

-- Initial content for home and about pages
INSERT INTO `page_content` (`page_name`, `content`) VALUES ('home', 'Welcome to our NGO! This is the default home page content. Please update it from the admin panel.');
INSERT INTO `page_content` (`page_name`, `content`) VALUES ('about_us', 'This is the default About Us page content. Learn more about our mission and vision here. Please update it from the admin panel.');

-- Blog Categories Table
CREATE TABLE `blog_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blog Posts Table
CREATE TABLE `blog_posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `image_url` VARCHAR(255) NULL,
  `author` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `blog_categories`(`id`) ON DELETE SET NULL
);
