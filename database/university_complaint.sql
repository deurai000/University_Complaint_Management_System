
-- ==========================================
-- UNIVERSITY COMPLAINT MANAGEMENT SYSTEM
-- DATABASE STRUCTURE
-- ==========================================

CREATE DATABASE IF NOT EXISTS university_complaint_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE university_complaint_system;


-- ==========================================
-- 1. USERS TABLE
-- Stores students, staff and administrators
-- ==========================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,

    full_name VARCHAR(150) NOT NULL,

    email VARCHAR(150) NOT NULL UNIQUE,
    email_verified_at DATETIME NULL,

    password VARCHAR(255) NOT NULL,

    student_number VARCHAR(50) UNIQUE,

    department VARCHAR(100),

    role ENUM('student', 'staff', 'admin')
        NOT NULL DEFAULT 'student',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ==========================================
CREATE TABLE IF NOT EXISTS password_resets (
    user_id INT PRIMARY KEY,
    token_hash CHAR(64) NOT NULL UNIQUE,
    requested_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 2. CATEGORIES TABLE
-- Types of complaints
-- ==========================================

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,

    category_name VARCHAR(100) NOT NULL UNIQUE,

    description TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ==========================================
-- 3. COMPLAINTS TABLE
-- Stores student complaints
-- ==========================================

CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,

    student_id INT NOT NULL,

    category_id INT NOT NULL,

    title VARCHAR(200) NOT NULL,
    next_update DATE NULL,

    description TEXT NOT NULL,

    priority ENUM('low', 'medium', 'high')
        NOT NULL DEFAULT 'medium',

    status ENUM(
        'pending',
        'in_progress',
        'resolved',
        'rejected'
    ) NOT NULL DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (student_id)
        REFERENCES users(id),

    FOREIGN KEY (category_id)
        REFERENCES categories(id),

    INDEX idx_complaint_student (student_id),
    INDEX idx_complaint_status (status)
);


-- ==========================================
-- 4. COMPLAINT ASSIGNMENTS TABLE
-- Records which staff member handles a complaint
-- ==========================================

CREATE TABLE IF NOT EXISTS complaint_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,

    complaint_id INT NOT NULL,

    staff_id INT NOT NULL,

    assigned_by INT NOT NULL,

    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (complaint_id)
        REFERENCES complaints(id),

    FOREIGN KEY (staff_id)
        REFERENCES users(id),

    FOREIGN KEY (assigned_by)
        REFERENCES users(id),

    INDEX idx_assignment_staff (staff_id)
);


-- ==========================================
-- 5. COMPLAINT UPDATES TABLE
-- Records responses and progress history
-- ==========================================

CREATE TABLE IF NOT EXISTS complaint_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,

    complaint_id INT NOT NULL,

    updated_by INT NOT NULL,

    message TEXT NOT NULL,

    old_status VARCHAR(30),

    new_status VARCHAR(30),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (complaint_id)
        REFERENCES complaints(id),

    FOREIGN KEY (updated_by)
        REFERENCES users(id),

    INDEX idx_update_complaint (complaint_id)
);


-- ==========================================
-- 6. COMPLAINT ATTACHMENTS TABLE
-- Stores uploaded file information
-- ==========================================

CREATE TABLE IF NOT EXISTS complaint_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,

    complaint_id INT NOT NULL,

    uploaded_by INT NOT NULL,

    file_name VARCHAR(255) NOT NULL,

    file_path VARCHAR(500) NOT NULL,

    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (complaint_id)
        REFERENCES complaints(id),

    FOREIGN KEY (uploaded_by)
        REFERENCES users(id)
);


-- ==========================================
-- INSERT DEFAULT COMPLAINT CATEGORIES
-- ==========================================

INSERT IGNORE INTO categories
(category_name, description)
VALUES
('Academic', 'Issues related to courses, exams and grades'),

('Facilities', 'Issues related to classrooms and university facilities'),

('Accommodation', 'Dormitory and housing-related complaints'),

('IT Services', 'Internet, computer and technical problems'),

('Administration', 'Administrative and university service issues'),

('Other', 'Other university-related complaints');


-- ==========================================
-- DATABASE SETUP COMPLETED
-- ==========================================
