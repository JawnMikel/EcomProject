-- GAINZ Fitness App - Complete Database Schema
-- Run this ONCE in phpMyAdmin or MySQL CLI to set up your database

CREATE DATABASE IF NOT EXISTS gainz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gainz;

-- =====================
-- USERS
-- =====================
CREATE TABLE users (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    username         VARCHAR(50)  NOT NULL UNIQUE,
    email            VARCHAR(255) NOT NULL UNIQUE,
    password_hash    VARCHAR(255) NOT NULL,
    two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0,
    two_factor_secret  VARCHAR(64)  DEFAULT NULL,
    birth_date       DATE         NOT NULL,
    role             ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    bio              TEXT         DEFAULT NULL,
    profile_picture  VARCHAR(500) DEFAULT NULL,
    fitness_goal     VARCHAR(100) DEFAULT NULL,
    experience_level ENUM('beginner','intermediate','advanced') DEFAULT 'beginner',
    height           DECIMAL(5,2) DEFAULT NULL,
    weight_goal      DECIMAL(5,2) DEFAULT NULL,
    workout_frequency VARCHAR(50) DEFAULT NULL,
    preferred_days   VARCHAR(50)  DEFAULT NULL,
    location         VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================
-- BODY WEIGHT ENTRIES
-- =====================
CREATE TABLE body_weight_entries (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT            NOT NULL,
    weight_value DECIMAL(5,2)   NOT NULL,
    entry_date   DATE           NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================
-- CATEGORIES
-- =====================
CREATE TABLE categories (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================
-- EXERCISES
-- =====================
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

-- =====================
-- TRAINING PROGRAMS
-- =====================
CREATE TABLE training_programs (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    user_id            INT          DEFAULT NULL,
    name               VARCHAR(150) NOT NULL,
    description        TEXT         DEFAULT NULL,
    is_system_template TINYINT(1)   NOT NULL DEFAULT 0,
    difficulty         ENUM('beginner','intermediate','advanced') DEFAULT 'beginner',
    environment        ENUM('gym','home','cardio') DEFAULT 'gym',
    goal               VARCHAR(50)  DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================
-- PROGRAM WORKOUTS
-- =====================
CREATE TABLE program_workouts (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    program_id   INT          NOT NULL,
    day_number   INT          NOT NULL,
    workout_name VARCHAR(150) NOT NULL,
    FOREIGN KEY (program_id) REFERENCES training_programs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================
-- PROGRAM WORKOUT EXERCISES
-- =====================
CREATE TABLE program_workout_exercises (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    workout_id     INT          NOT NULL,
    exercise_id    INT          NOT NULL,
    exercise_order INT          NOT NULL DEFAULT 0,
    target_sets    INT          DEFAULT NULL,
    target_reps    INT          DEFAULT NULL,
    FOREIGN KEY (workout_id)  REFERENCES program_workouts(id)  ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id)        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================
-- WORKOUT SESSIONS
-- =====================
CREATE TABLE workout_sessions (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT            NOT NULL,
    program_workout_id INT          DEFAULT NULL,
    start_time       DATETIME       NOT NULL,
    end_time         DATETIME       DEFAULT NULL,
    total_duration   INT            DEFAULT NULL,
    total_volume     DECIMAL(10,2)  DEFAULT NULL,
    notes            TEXT           DEFAULT NULL,
    FOREIGN KEY (user_id)            REFERENCES users(id)            ON DELETE CASCADE,
    FOREIGN KEY (program_workout_id) REFERENCES program_workouts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================
-- WORKOUT SESSION ITEMS
-- =====================
CREATE TABLE workout_session_items (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    session_id    INT            NOT NULL,
    exercise_id   INT            NOT NULL,
    set_number    INT            NOT NULL,
    reps          INT            DEFAULT NULL,
    weight        DECIMAL(6,2)   DEFAULT NULL,
    set_type      ENUM('normal','warmup','drop','failure') NOT NULL DEFAULT 'normal',
    rest_duration INT            DEFAULT NULL,
    FOREIGN KEY (session_id)  REFERENCES workout_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id)        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================
-- SEED DATA: Categories
-- =====================
INSERT INTO categories (name) VALUES
    ('Chest'),
    ('Back'),
    ('Shoulders'),
    ('Arms'),
    ('Legs'),
    ('Core'),
    ('Cardio'),
    ('Full Body');

-- =====================
-- SEED DATA: Exercises
-- =====================
INSERT INTO exercises (category_id, name, description, difficulty, equipment) VALUES
    (1, 'Bench Press', 'A compound exercise that targets the chest, shoulders, and triceps.', 'intermediate', 'Barbell'),
    (1, 'Incline Bench Press', 'Targets the upper chest with an inclined bench.', 'intermediate', 'Barbell'),
    (1, 'Dumbbell Flyes', 'Isolation exercise for the chest muscles.', 'beginner', 'Dumbbells'),
    (1, 'Push-ups', 'Bodyweight exercise for chest and triceps.', 'beginner', 'None'),
    (2, 'Pull-ups', 'Bodyweight exercise targeting the back and biceps.', 'advanced', 'Pull-up Bar'),
    (2, 'Barbell Row', 'Compound exercise for back thickness.', 'intermediate', 'Barbell'),
    (2, 'Lat Pulldown', 'Machine exercise for lat development.', 'beginner', 'Cable Machine'),
    (2, 'Deadlift', 'Compound movement that works the entire posterior chain.', 'advanced', 'Barbell'),
    (3, 'Overhead Press', 'Shoulder exercise that also engages the core.', 'intermediate', 'Barbell'),
    (3, 'Lateral Raises', 'Isolation exercise for side deltoids.', 'beginner', 'Dumbbells'),
    (3, 'Face Pulls', 'Cable exercise for rear deltoids and upper back.', 'beginner', 'Cable'),
    (4, 'Bicep Curls', 'Isolation exercise for the biceps.', 'beginner', 'Dumbbells'),
    (4, 'Tricep Dips', 'Bodyweight exercise targeting triceps.', 'beginner', 'Bench'),
    (4, 'Hammer Curls', 'Bicep exercise with neutral grip.', 'beginner', 'Dumbbells'),
    (4, 'Skull Crushers', 'Tricep isolation exercise.', 'intermediate', 'EZ Bar'),
    (5, 'Squat', 'Fundamental lower body exercise for quads and glutes.', 'intermediate', 'Barbell'),
    (5, 'Leg Press', 'Machine exercise for leg development.', 'beginner', 'Leg Press Machine'),
    (5, 'Lunges', 'Unilateral leg exercise for balance.', 'beginner', 'Dumbbells'),
    (5, 'Leg Curl', 'Isolation exercise for hamstrings.', 'beginner', 'Leg Curl Machine'),
    (5, 'Calf Raises', 'Isolation exercise for calves.', 'beginner', 'Calf Machine'),
    (6, 'Plank', 'Isometric core exercise.', 'beginner', 'None'),
    (6, 'Crunches', 'Basic ab exercise.', 'beginner', 'None'),
    (6, 'Russian Twist', 'Core rotation exercise.', 'intermediate', 'None'),
    (7, 'Running', 'Cardio exercise for endurance.', 'beginner', 'Treadmill'),
    (7, 'Jump Rope', 'Cardio exercise for coordination.', 'beginner', 'Jump Rope'),
    (8, 'Burpees', 'Full-body exercise combining squat, plank, and jump.', 'advanced', 'None'),
    (8, 'Kettlebell Swing', 'Full-body power exercise.', 'intermediate', 'Kettlebell');

-- =====================
-- SEED DATA: Demo User (password: password123)
-- =====================
INSERT INTO users (username, email, password_hash, birth_date, role) VALUES
    ('demo', 'demo@gainz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2000-01-01', 'user');

-- =====================
-- SEED DATA: Explore Programs (Templates)
-- =====================
INSERT INTO training_programs (name, description, is_system_template, difficulty, environment, goal) VALUES
    ('Push Day Basics', 'Classic push workout for chest, shoulders, and triceps', 1, 'beginner', 'gym', 'muscle'),
    ('Pull Day Power', 'Build your back and biceps with this effective routine', 1, 'beginner', 'gym', 'muscle'),
    ('Leg Day Fundamentals', 'Build lower body strength with squats and more', 1, 'beginner', 'gym', 'strength'),
    ('Full Body Home', 'No equipment? No problem. Full body workout at home', 1, 'beginner', 'home', 'general'),
    ('HIIT Cardio Blast', 'High intensity interval training for fat loss', 1, 'intermediate', 'cardio', 'weight_loss'),
    ('5k Runner Prep', 'Training plan to help you run your first 5k', 1, 'beginner', 'cardio', 'endurance'),
    ('Bodyweight Basics', 'Build strength using only your body weight', 1, 'beginner', 'home', 'strength'),
    ('Gym Beginner', 'Perfect starting point for new gym-goers', 1, 'beginner', 'gym', 'general'),
    ('Advanced Hypertrophy', 'High volume muscle building program', 1, 'advanced', 'gym', 'muscle'),
    ('Core Crusher', 'Intensive core and abs workout', 1, 'intermediate', 'home', 'strength');

-- Get the program IDs and add workouts/exercises
SET @p1 = (SELECT id FROM training_programs WHERE name = 'Push Day Basics');
SET @p2 = (SELECT id FROM training_programs WHERE name = 'Pull Day Power');
SET @p3 = (SELECT id FROM training_programs WHERE name = 'Leg Day Fundamentals');
SET @p4 = (SELECT id FROM training_programs WHERE name = 'Full Body Home');
SET @p5 = (SELECT id FROM training_programs WHERE name = 'HIIT Cardio Blast');
SET @p6 = (SELECT id FROM training_programs WHERE name = '5k Runner Prep');
SET @p7 = (SELECT id FROM training_programs WHERE name = 'Bodyweight Basics');
SET @p8 = (SELECT id FROM training_programs WHERE name = 'Gym Beginner');
SET @p9 = (SELECT id FROM training_programs WHERE name = 'Advanced Hypertrophy');
SET @p10 = (SELECT id FROM training_programs WHERE name = 'Core Crusher');

-- Push Day
INSERT INTO program_workouts (program_id, day_number, workout_name) VALUES (@p1, 1, 'Push Day');
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 1, 3, 10 FROM program_workouts pw JOIN exercises e ON e.name = 'Bench Press' WHERE pw.program_id = @p1;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 2, 3, 12 FROM program_workouts pw JOIN exercises e ON e.name = 'Incline Bench Press' WHERE pw.program_id = @p1;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 3, 3, 10 FROM program_workouts pw JOIN exercises e ON e.name = 'Overhead Press' WHERE pw.program_id = @p1;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 4, 3, 12 FROM program_workouts pw JOIN exercises e ON e.name = 'Lateral Raises' WHERE pw.program_id = @p1;

-- Pull Day
INSERT INTO program_workouts (program_id, day_number, workout_name) VALUES (@p2, 1, 'Pull Day');
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 1, 3, 8 FROM program_workouts pw JOIN exercises e ON e.name = 'Pull-ups' WHERE pw.program_id = @p2;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 2, 3, 10 FROM program_workouts pw JOIN exercises e ON e.name = 'Barbell Row' WHERE pw.program_id = @p2;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 3, 3, 12 FROM program_workouts pw JOIN exercises e ON e.name = 'Lat Pulldown' WHERE pw.program_id = @p2;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 4, 3, 12 FROM program_workouts pw JOIN exercises e ON e.name = 'Bicep Curls' WHERE pw.program_id = @p2;

-- Leg Day
INSERT INTO program_workouts (program_id, day_number, workout_name) VALUES (@p3, 1, 'Leg Day');
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 1, 4, 10 FROM program_workouts pw JOIN exercises e ON e.name = 'Squat' WHERE pw.program_id = @p3;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 2, 3, 12 FROM program_workouts pw JOIN exercises e ON e.name = 'Leg Press' WHERE pw.program_id = @p3;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 3, 3, 12 FROM program_workouts pw JOIN exercises e ON e.name = 'Lunges' WHERE pw.program_id = @p3;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 4, 3, 15 FROM program_workouts pw JOIN exercises e ON e.name = 'Leg Curl' WHERE pw.program_id = @p3;

-- Full Body Home
INSERT INTO program_workouts (program_id, day_number, workout_name) VALUES (@p4, 1, 'Full Body');
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 1, 3, 15 FROM program_workouts pw JOIN exercises e ON e.name = 'Push-ups' WHERE pw.program_id = @p4;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 2, 3, 12 FROM program_workouts pw JOIN exercises e ON e.name = 'Burpees' WHERE pw.program_id = @p4;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 3, 3, 20 FROM program_workouts pw JOIN exercises e ON e.name = 'Crunches' WHERE pw.program_id = @p4;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 4, 3, 30 FROM program_workouts pw JOIN exercises e ON e.name = 'Jump Rope' WHERE pw.program_id = @p4;

-- HIIT Cardio
INSERT INTO program_workouts (program_id, day_number, workout_name) VALUES (@p5, 1, 'HIIT');
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 1, 10, 1 FROM program_workouts pw JOIN exercises e ON e.name = 'Burpees' WHERE pw.program_id = @p5;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 2, 10, 1 FROM program_workouts pw JOIN exercises e ON e.name = 'Jump Rope' WHERE pw.program_id = @p5;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 3, 10, 1 FROM program_workouts pw JOIN exercises e ON e.name = 'Running' WHERE pw.program_id = @p5;

-- 5k Runner
INSERT INTO program_workouts (program_id, day_number, workout_name) VALUES (@p6, 1, 'Run Training');
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 1, 1, 30 FROM program_workouts pw JOIN exercises e ON e.name = 'Running' WHERE pw.program_id = @p6;

-- Bodyweight Basics
INSERT INTO program_workouts (program_id, day_number, workout_name) VALUES (@p7, 1, 'Bodyweight');
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 1, 3, 10 FROM program_workouts pw JOIN exercises e ON e.name = 'Push-ups' WHERE pw.program_id = @p7;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 2, 3, 10 FROM program_workouts pw JOIN exercises e ON e.name = 'Pull-ups' WHERE pw.program_id = @p7;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 3, 3, 60 FROM program_workouts pw JOIN exercises e ON e.name = 'Plank' WHERE pw.program_id = @p7;

-- Gym Beginner
INSERT INTO program_workouts (program_id, day_number, workout_name) VALUES (@p8, 1, 'First Gym');
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 1, 2, 10 FROM program_workouts pw JOIN exercises e ON e.name = 'Bench Press' WHERE pw.program_id = @p8;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 2, 2, 10 FROM program_workouts pw JOIN exercises e ON e.name = 'Squat' WHERE pw.program_id = @p8;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 3, 2, 10 FROM program_workouts pw JOIN exercises e ON e.name = 'Deadlift' WHERE pw.program_id = @p8;

-- Advanced Hypertrophy
INSERT INTO program_workouts (program_id, day_number, workout_name) VALUES (@p9, 1, 'Hypertrophy');
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 1, 5, 8 FROM program_workouts pw JOIN exercises e ON e.name = 'Bench Press' WHERE pw.program_id = @p9;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 2, 4, 10 FROM program_workouts pw JOIN exercises e ON e.name = 'Incline Bench Press' WHERE pw.program_id = @p9;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 3, 4, 10 FROM program_workouts pw JOIN exercises e ON e.name = 'Dumbbell Flyes' WHERE pw.program_id = @p9;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 4, 4, 12 FROM program_workouts pw JOIN exercises e ON e.name = 'Skull Crushers' WHERE pw.program_id = @p9;

-- Core Crusher
INSERT INTO program_workouts (program_id, day_number, workout_name) VALUES (@p10, 1, 'Core');
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 1, 3, 60 FROM program_workouts pw JOIN exercises e ON e.name = 'Plank' WHERE pw.program_id = @p10;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 2, 3, 20 FROM program_workouts pw JOIN exercises e ON e.name = 'Crunches' WHERE pw.program_id = @p10;
INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
SELECT pw.id, e.id, 3, 3, 20 FROM program_workouts pw JOIN exercises e ON e.name = 'Russian Twist' WHERE pw.program_id = @p10;