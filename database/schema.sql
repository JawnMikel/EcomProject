-- GAINZ Fitness App - Database Schema
-- Run this in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS gainz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gainz;

-- --------------------------------------------------------
-- Users
-- --------------------------------------------------------
CREATE TABLE users (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    username         VARCHAR(50)  NOT NULL UNIQUE,
    email            VARCHAR(255) NOT NULL UNIQUE,
    password_hash    VARCHAR(255) NOT NULL,
    two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0,
    two_factor_secret  VARCHAR(64)  DEFAULT NULL,
    birth_date       DATE         NOT NULL,
    role             ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Body Weight Entries
-- --------------------------------------------------------
CREATE TABLE body_weight_entries (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT            NOT NULL,
    weight_value DECIMAL(5,2)   NOT NULL,
    entry_date   DATE           NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Categories
-- --------------------------------------------------------
CREATE TABLE categories (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Exercises
-- --------------------------------------------------------
CREATE TABLE exercises (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT          NOT NULL,
    name        VARCHAR(150) NOT NULL,
    description TEXT         DEFAULT NULL,
    difficulty  ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
    equipment   VARCHAR(100) DEFAULT NULL,
    media_url   VARCHAR(500) DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Training Programs
-- --------------------------------------------------------
CREATE TABLE training_programs (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    user_id            INT          DEFAULT NULL,          -- NULL = system template
    name               VARCHAR(150) NOT NULL,
    description        TEXT         DEFAULT NULL,
    is_system_template TINYINT(1)   NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Program Workouts (days inside a program)
-- --------------------------------------------------------
CREATE TABLE program_workouts (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    program_id   INT          NOT NULL,
    day_number   INT          NOT NULL,
    workout_name VARCHAR(150) NOT NULL,
    FOREIGN KEY (program_id) REFERENCES training_programs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Workout Sessions
-- --------------------------------------------------------
CREATE TABLE workout_sessions (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT            NOT NULL,
    program_workout_id INT          DEFAULT NULL,          -- NULL = free session
    start_time       DATETIME       NOT NULL,
    end_time         DATETIME       DEFAULT NULL,
    total_duration   INT            DEFAULT NULL,          -- seconds
    total_volume     DECIMAL(10,2)  DEFAULT NULL,          -- kg * reps summed
    notes            TEXT           DEFAULT NULL,
    FOREIGN KEY (user_id)            REFERENCES users(id)            ON DELETE CASCADE,
    FOREIGN KEY (program_workout_id) REFERENCES program_workouts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Workout Session Items (individual sets)
-- --------------------------------------------------------
CREATE TABLE workout_session_items (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    session_id    INT            NOT NULL,
    exercise_id   INT            NOT NULL,
    set_number    INT            NOT NULL,
    reps          INT            DEFAULT NULL,
    weight        DECIMAL(6,2)   DEFAULT NULL,             -- kg
    set_type      ENUM('normal','warmup','drop','failure') NOT NULL DEFAULT 'normal',
    rest_duration INT            DEFAULT NULL,             -- seconds
    FOREIGN KEY (session_id)  REFERENCES workout_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id)        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Seed: default categories
-- --------------------------------------------------------
INSERT INTO categories (name) VALUES
    ('Chest'),
    ('Back'),
    ('Shoulders'),
    ('Arms'),
    ('Legs'),
    ('Core'),
    ('Cardio'),
    ('Full Body');
