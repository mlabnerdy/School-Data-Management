CREATE DATABASE IF NOT EXISTS school_data_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_data_management;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    username VARCHAR(80) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Administrator','Authorized Staff') NOT NULL DEFAULT 'Administrator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS students (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    photo VARCHAR(255) NULL,
    full_name VARCHAR(150) NOT NULL,
    date_of_birth DATE NULL,
    gender VARCHAR(30) NULL,
    address TEXT NULL,
    contact_number VARCHAR(50) NULL,
    parent_guardian VARCHAR(150) NULL,
    grade_section VARCHAR(100) NULL,
    other_info TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS teachers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL UNIQUE,
    photo VARCHAR(255) NULL,
    full_name VARCHAR(150) NOT NULL,
    date_of_birth DATE NULL,
    gender VARCHAR(30) NULL,
    address TEXT NULL,
    contact_number VARCHAR(50) NULL,
    email VARCHAR(150) NULL,
    position_department VARCHAR(150) NULL,
    other_info TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS staff (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL UNIQUE,
    photo VARCHAR(255) NULL,
    full_name VARCHAR(150) NOT NULL,
    date_of_birth DATE NULL,
    gender VARCHAR(30) NULL,
    address TEXT NULL,
    contact_number VARCHAR(50) NULL,
    email VARCHAR(150) NULL,
    position_department VARCHAR(150) NULL,
    other_info TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_type ENUM('student','teacher','staff') NOT NULL,
    owner_id INT UNSIGNED NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(100) NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_owner (owner_type, owner_id)
);

-- Default account: admin / admin123
INSERT IGNORE INTO users (full_name, username, password, role)
VALUES ('System Administrator', 'admin', '$2y$10$8J7r1Q5VxY8kKpY5d9m2UO2bR9VQm1cYfJm0QqgXqJqV9y0Kx8a4e', 'Administrator');
