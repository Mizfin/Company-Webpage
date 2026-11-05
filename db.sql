-- ============================================
--  Al-Hind Solutions - Database Setup (v2)
--  Added: status column for admin approval
--  Run this in phpMyAdmin (XAMPP)
--  Steps:
--    1. Open http://localhost/phpmyadmin
--    2. Click "SQL" tab at the top
--    3. Paste this entire script and click "Go"
-- ============================================

-- Drop old database and recreate (fresh start)
DROP DATABASE IF EXISTS alhind_db;
CREATE DATABASE alhind_db;
USE alhind_db;

-- Users table (employees and admins)
-- status: 'pending' = waiting for admin approval
--         'approved' = can login
--         'rejected' = blocked by admin
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    fullname   VARCHAR(100)                              NOT NULL,
    email      VARCHAR(100) UNIQUE                       NOT NULL,
    password   VARCHAR(255)                              NOT NULL,
    role       ENUM('employee', 'admin')                 DEFAULT 'employee',
    status     ENUM('pending', 'approved', 'rejected')  DEFAULT 'pending',
    department VARCHAR(100)                              DEFAULT '',
    phone      VARCHAR(20)                               DEFAULT '',
    created_at TIMESTAMP                                 DEFAULT CURRENT_TIMESTAMP
);

-- Default Admin account (auto-approved)
-- Login: admin@al-hindsolutions.com / admin123
INSERT INTO users (fullname, email, password, role, status, department, phone)
VALUES (
    'Admin User',
    'admin@al-hindsolutions.com',
    SHA2('admin123', 256),
    'admin',
    'approved',
    'Management',
    '9000000000'
);

-- Sample Employee account (auto-approved for demo)
-- Login: alice@al-hindsolutions.com / employee123
INSERT INTO users (fullname, email, password, role, status, department, phone)
VALUES (
    'Alice Brown',
    'alice@al-hindsolutions.com',
    SHA2('employee123', 256),
    'employee',
    'approved',
    'IT',
    '9876543210'
);
