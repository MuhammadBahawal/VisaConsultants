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

-- Insert sample admin user (password: admin123)
-- Replace the placeholder below with a real bcrypt hash generated locally.
-- Generate hash locally with: php -r "echo password_hash('admin123', PASSWORD_BCRYPT).PHP_EOL;"

INSERT INTO users (username, email, password) VALUES 
('ahsan@911', 'ahsan@gmail.com', '$2y$10$1sUj6JAfkGS06pVAf9D9rOb2vOiZm63vxeNU2TAht6MrZWTrlYl36');

-- Or after importing run (replace <HASH> with generated hash):
-- UPDATE users SET password = '<HASH>' WHERE email = 'admin@amvisa.com';

-- Insert sample blogs
INSERT INTO blogs (title, slug, category, image_url, short_description, content, author_id) VALUES
('ANSO Scholarship In China For Pakistani Students 2026', 'anso-scholarship-china-2026', 'Scholarship', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=500&h=300&fit=crop', 'Explore ANSO Scholarship opportunities in China for Pakistani students.', 'Complete guide about ANSO Scholarship in China for Pakistani students in 2026...', 1),
('UCAS International Summer School China For Pakistani 2026', 'ucas-summer-school-2026', 'Education', 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?w=500&h=300&fit=crop', 'UCAS International Summer School in China for Pakistani students.', 'Detailed information about UCAS International Summer School in China...', 1);