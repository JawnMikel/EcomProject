/**
 * GAINZ Database Schema
 * MySQL/MariaDB schema for the fitness tracking application
 */

-- Create database
CREATE DATABASE IF NOT EXISTS gainz;
USE gainz;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    age INT NOT NULL CHECK (age >= 16),
    language VARCHAR(5) DEFAULT 'en',
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255),
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);

-- Exercises table
CREATE TABLE exercises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100) NOT NULL,
    muscle_group VARCHAR(100) NOT NULL,
    instructions TEXT,
    image_url VARCHAR(500),
    video_url VARCHAR(500),
    difficulty INT DEFAULT 1 CHECK (difficulty BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_muscle_group (muscle_group)
);

-- Training Programs table
CREATE TABLE training_programs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    duration_weeks INT NOT NULL,
    difficulty_level INT CHECK (difficulty_level BETWEEN 1 AND 5),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_difficulty (difficulty_level)
);

-- Program Exercises (junction table)
CREATE TABLE program_exercises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    program_id INT NOT NULL,
    exercise_id INT NOT NULL,
    week INT NOT NULL,
    day INT NOT NULL,
    sets INT NOT NULL,
    reps INT,
    duration INT,
    notes TEXT,
    FOREIGN KEY (program_id) REFERENCES training_programs(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE
);

-- Workouts table (logged workouts)
CREATE TABLE workouts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    name VARCHAR(255) NOT NULL,
    notes TEXT,
    duration INT NOT NULL,
    bodyweight DECIMAL(5, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_date (date)
);

-- Workout Exercises (exercises performed in a workout)
CREATE TABLE workout_exercises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    workout_id INT NOT NULL,
    exercise_id INT NOT NULL,
    sets JSON NOT NULL,
    notes TEXT,
    FOREIGN KEY (workout_id) REFERENCES workouts(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE
);

-- User Analytics/Progress table
CREATE TABLE user_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    bodyweight DECIMAL(5, 2),
    total_workouts INT DEFAULT 0,
    total_duration INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, date)
);
