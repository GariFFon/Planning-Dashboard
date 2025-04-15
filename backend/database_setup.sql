-- Create database if not exists
CREATE DATABASE IF NOT EXISTS event_planning;

-- Use the database
USE event_planning;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL,
    INDEX idx_email (email)
);

-- Events table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    status ENUM('pending', 'confirmed', 'canceled', 'completed') DEFAULT 'pending',
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, event_date)
);

-- Event categories table
CREATE TABLE IF NOT EXISTS event_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(20) DEFAULT '#6B7280',
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Event-category relationship table
CREATE TABLE IF NOT EXISTS event_category_map (
    event_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (event_id, category_id),
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES event_categories(id) ON DELETE CASCADE
);

-- Event tasks table
CREATE TABLE IF NOT EXISTS event_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    task_name VARCHAR(100) NOT NULL,
    status ENUM('pending', 'in-progress', 'completed') DEFAULT 'pending',
    due_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event_status (event_id, status)
);

-- Event attendees table
CREATE TABLE IF NOT EXISTS event_attendees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    status ENUM('invited', 'confirmed', 'declined') DEFAULT 'invited',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event_status (event_id, status)
);

-- User settings table
CREATE TABLE IF NOT EXISTS user_settings (
    user_id INT PRIMARY KEY,
    theme VARCHAR(20) DEFAULT 'dark',
    notification_email BOOLEAN DEFAULT TRUE,
    calendar_view VARCHAR(20) DEFAULT 'month',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Remember me tokens table
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (token),
    INDEX idx_user_token (user_id, token)
);

-- Insert a default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@example.com', '$2y$10$2TfB0mP2SjMX4Wnr.OdKpeC9Z17Gm0E9aOYXLLBuIXWoGos2hiZQS', 'admin');

-- Insert some default event categories for the admin
INSERT INTO event_categories (name, color, user_id) VALUES 
('Meeting', '#6366F1', 1),
('Conference', '#F59E0B', 1),
('Workshop', '#10B981', 1),
('Social', '#EC4899', 1),
('Holiday', '#3B82F6', 1);

-- Insert sample events for the admin user
INSERT INTO events (name, description, event_date, event_time, location, status, user_id) VALUES 
('Team Meeting', 'Weekly team sync-up to discuss progress and roadblocks', '2025-04-20', '10:00:00', 'Conference Room A', 'confirmed', 1),
('Product Launch', 'Launch of our new product line with press and stakeholders', '2025-05-15', '14:00:00', 'Main Auditorium', 'pending', 1),
('Client Workshop', 'Training workshop for new client onboarding', '2025-04-25', '09:30:00', 'Training Center', 'confirmed', 1),
('Company Anniversary', 'Celebration of company milestone', '2025-06-10', '18:00:00', 'Grand Ballroom, Hilton Hotel', 'pending', 1);

-- Map events to categories
INSERT INTO event_category_map (event_id, category_id) VALUES 
(1, 1), -- Team Meeting -> Meeting
(2, 4), -- Product Launch -> Social
(3, 3), -- Client Workshop -> Workshop
(4, 4); -- Company Anniversary -> Social

-- Create a few sample tasks for events
INSERT INTO event_tasks (event_id, task_name, status, due_date) VALUES 
(2, 'Prepare press kit', 'pending', '2025-05-01'),
(2, 'Send invitations', 'completed', '2025-04-25'),
(4, 'Book venue', 'completed', '2025-04-01'),
(4, 'Order catering', 'in-progress', '2025-05-20');

-- Add some sample attendees
INSERT INTO event_attendees (event_id, name, email, status) VALUES 
(1, 'John Doe', 'john@example.com', 'confirmed'),
(1, 'Jane Smith', 'jane@example.com', 'confirmed'),
(2, 'Press Contact', 'press@media.com', 'invited'),
(4, 'CEO', 'ceo@company.com', 'confirmed');

-- Set user settings for admin
INSERT INTO user_settings (user_id, theme, notification_email) VALUES 
(1, 'dark', TRUE);