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

-- Insert for Contact Page Content
INSERT INTO `page_content` (`page_name`, `content`) VALUES ('contact_us', '{
    "ngo_name": "Hope for All",
    "address": "456 Charity Avenue",
    "city_state_zip": "Kindness, KND 12345",
    "country": "Republic of Goodwill",
    "phone": "(987) 654-3210",
    "email": "contact@hopeforall.org",
    "website": "www.hopeforall.org",
    "office_hours_mf": "8:30 AM - 5:30 PM",
    "office_hours_ss": "Closed on weekends"
}')
ON DUPLICATE KEY UPDATE `page_name` = `page_name`; -- Do nothing if it already exists
