CREATE DATABASE vehicle_management;

USE vehicle_management;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    national_id VARCHAR(20) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    registration_number VARCHAR(20) UNIQUE NOT NULL,
    make_model VARCHAR(100) NOT NULL,
    year_of_manufacture YEAR,
    color VARCHAR(50),
    engine_number VARCHAR(50),
    chassis_number VARCHAR(50),
    total_distance INT DEFAULT 0,
    next_inspection_date DATE,
    insurance_expiry DATE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE driver_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    car_reg VARCHAR(20) NOT NULL,
    car_model VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE maintenance_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT,
    service_date DATE NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    description TEXT,
    cost DECIMAL(10,2) NOT NULL,
    next_service_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);

CREATE TABLE insurance_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT,
    policy_number VARCHAR(50) UNIQUE NOT NULL,
    provider VARCHAR(100) NOT NULL,
    coverage_type VARCHAR(50) NOT NULL,
    premium_amount DECIMAL(10,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);

CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    reset_token VARCHAR(255) UNIQUE NOT NULL,
    expiry_date TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE traffic_violations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    driver_id INT,
    violation_date DATE NOT NULL,
    violation_type VARCHAR(100) NOT NULL,
    penalty_amount DECIMAL(10,2) NOT NULL,
    points_deducted INT DEFAULT 0,
    location VARCHAR(255),
    status ENUM('pending', 'paid', 'disputed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES driver_details(id)
);
  