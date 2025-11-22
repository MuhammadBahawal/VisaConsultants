-- Create database
CREATE DATABASE IF NOT EXISTS visa_consultants;
USE visa_consultants;

-- Users table for admin login
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blogs table
CREATE TABLE IF NOT EXISTS blogs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    category VARCHAR(100),
    image_url VARCHAR(500),
    short_description TEXT,
    content LONGTEXT,
    author_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);

-- Contacts table
CREATE TABLE IF NOT EXISTS contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample admin user (password: admin123)
-- Replace the placeholder below with a real bcrypt hash generated locally.
-- Generate hash locally with: php -r "echo password_hash('admin123', PASSWORD_BCRYPT).PHP_EOL;"

INSERT INTO users (username, email, password) VALUES 
('ahsan@911', 'ahsan@gmail.com', '$2y$10$1sUj6JAfkGS06pVAf9D9rOb2vOiZm63vxeNU2TAht6MrZWTrlYl36');

-- Or after importing run (replace <HASH> with generated hash):
-- UPDATE users SET password = '<HASH>' WHERE email = 'ahsan@gmail.com';

-- Subscriptions table
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- YouTube Videos table
CREATE TABLE IF NOT EXISTS youtube_videos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    youtube_url VARCHAR(500) NOT NULL,
    thumbnail_url VARCHAR(500),
    is_active BOOLEAN DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE DATABASE course_enrollment;

USE course_enrollment;

CREATE TABLE enrollments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(50),
  last_name VARCHAR(50),
  email VARCHAR(100),
  phone VARCHAR(20),
  city VARCHAR(50),
  nationality VARCHAR(50),
  course VARCHAR(100),
  course_type VARCHAR(50),
  delivery_mode VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Insert sample blogs
INSERT INTO blogs (title, slug, category, image_url, short_description, content, author_id) VALUES
('ANSO Scholarship In China For Pakistani Students 2026', 'anso-scholarship-china-2026', 'Scholarship', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=500&h=300&fit=crop', 'Explore ANSO Scholarship opportunities in China for Pakistani students.', 'Complete guide about ANSO Scholarship in China for Pakistani students in 2026...', 1),
('UCAS International Summer School China For Pakistani 2026', 'ucas-summer-school-2026', 'Education', 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?w=500&h=300&fit=crop', 'UCAS International Summer School in China for Pakistani students.', 'Detailed information about UCAS International Summer School in China...', 1);