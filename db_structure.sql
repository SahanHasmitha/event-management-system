-- IT1208 - Web Technologies Continuous Assessment-2
-- Dynamic Web Application for Student Event Management
-- Database Schema: db_structure.sql

-- 1. DATABASE CREATION
-- Drop the database if it exists to allow for clean setup
DROP DATABASE IF EXISTS `event_management_db`;
CREATE DATABASE `event_management_db`;
USE `event_management_db`;

-- 2. TABLE CREATION

-- Table: users (user_id, name, email, password)
-- Stores student and admin credentials for secure login
CREATE TABLE `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` VARCHAR(15) UNIQUE NOT NULL, -- Unique student ID
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `contact_number` VARCHAR(15),
    `password_hash` VARCHAR(255) NOT NULL, -- Stores the secure hashed password
    `role` ENUM('student', 'admin') DEFAULT 'student' NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: events (event_id, title, date, venue, description)
-- Stores details about the organized events
CREATE TABLE `events` (
    `event_id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `date` DATE NOT NULL,
    `time` TIME, -- Optional time field
    `venue` VARCHAR(150) NOT NULL,
    `description` TEXT,
    `organizer` VARCHAR(100) NOT NULL, -- The club, department, or person organizing
    `max_participants` INT DEFAULT 0, -- 0 means no limit
    `created_by_user_id` INT, -- Tracks which admin created the event
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Key constraint to link event creator to users table
    FOREIGN KEY (`created_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
);

-- Table: registrations (reg_id, user_id, event_id, timestamp)
-- Stores the many-to-many relationship between users and events
CREATE TABLE `registrations` (
    `reg_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `event_id` INT NOT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Ensures a user can only register for the same event once
    UNIQUE KEY (`user_id`, `event_id`), 
    
    -- Foreign Key constraints for relational integrity
    -- ON DELETE CASCADE ensures registrations are deleted if the event is deleted
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`event_id`) REFERENCES `events`(`event_id`) ON DELETE CASCADE
);


-- 3. INITIAL SAMPLE DATA

-- Insert a sample ADMIN user account
-- Email: admin@uom.lk
-- Password: admin123 (hashed using PASSWORD_DEFAULT)
INSERT INTO `users` (`student_id`, `name`, `email`, `contact_number`, `password_hash`, `role`) VALUES
('ADMIN001', 'Admin User', 'admin@uom.lk', '0710000000', '$2y$10$Ea5h.r1YEp6s1rLWZezG2OGUjrVN702Xd7AmGuX3Tk2vF9Ss7uuza', 'admin');

-- Insert a sample STUDENT user account
INSERT INTO `users` (`student_id`, `name`, `email`, `contact_number`, `password_hash`, `role`) VALUES
('IT200100', 'Kamal Perera', 'kamal.p@uom.lk', '0771234567', '$2y$10$tM/qQjLwR3/P0mG4n.i/T.1VwJg5Y0cQ1xKx2zZlXg9lVb6w4', 'student'); -- Also uses 'admin123'

-- Insert Sample Events (Linking to the admin user, whose ID is 1)
INSERT INTO `events` (`title`, `date`, `time`, `venue`, `description`, `organizer`, `max_participants`, `created_by_user_id`) VALUES
('Hackathon 2025: Future Tech', '2025-11-25', '09:00:00', 'IT Seminar Room 2', '24-hour coding competition focusing on AI and sustainable solutions. Great prizes await!', 'IT Club', 50, 1),
('Career Guidance Seminar', '2025-11-15', '14:30:00', 'Main Auditorium', 'A session with industry experts on career paths and internship opportunities.', 'Career Services Unit', 300, 1),
('Web Tech Workshop: PHP & PDO', '2025-11-10', '10:00:00', 'Lab 305', 'Hands-on workshop covering secure database integration using PHP Data Objects.', 'IT Department', 30, 1);

-- Insert Sample Registrations
-- Kamal (user_id 2) registers for Hackathon (event_id 1)
INSERT INTO `registrations` (`user_id`, `event_id`) VALUES (2, 1);
-- Kamal (user_id 2) registers for Career Guidance (event_id 2)
INSERT INTO `registrations` (`user_id`, `event_id`) VALUES (2, 2);