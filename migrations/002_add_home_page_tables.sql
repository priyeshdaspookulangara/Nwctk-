-- Migration to add tables for banners and home page sections

CREATE TABLE `banners` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `subtitle` VARCHAR(255),
  `image_url` VARCHAR(255) NOT NULL,
  `link_url` VARCHAR(255),
  `display_order` INT DEFAULT 0,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `home_page_sections` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `section_name` VARCHAR(100) NOT NULL UNIQUE, -- e.g., 'introduction', 'why_us', 'activities'
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT,
  `image_url` VARCHAR(255),
  `display_order` INT DEFAULT 0,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Optional: Insert some sample data for testing
INSERT INTO `banners` (`title`, `subtitle`, `image_url`, `link_url`) VALUES
('Welcome to Our NGO', 'Making a difference, one step at a time.', 'uploads/banners/banner1.jpg', '/about.php'),
('Support Our Cause', 'Your donation can change lives.', 'uploads/banners/banner2.jpg', '/donations.php');

INSERT INTO `home_page_sections` (`section_name`, `title`, `content`) VALUES
('introduction', 'Introduction', 'This is the introduction to our NGO. We are dedicated to...'),
('why_us', 'Why Us', 'We have a proven track record of making a real impact...'),
('activities', 'Our Activities', 'We are involved in a variety of activities, including...');
