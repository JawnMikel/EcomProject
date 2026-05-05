-- GAINZ Database Schema
-- Personal Workout Tracking Platform

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    date_of_birth DATE NOT NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK(role IN ('user', 'admin')),
    two_factor_enabled BOOLEAN DEFAULT 0,
    two_factor_secret VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Categories table (muscle groups)
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name_en VARCHAR(100) NOT NULL,
    name_fr VARCHAR(100) NOT NULL,
    description_en TEXT,
    description_fr TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Exercises table
CREATE TABLE IF NOT EXISTS exercises (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER,
    name_en VARCHAR(255) NOT NULL,
    name_fr VARCHAR(255) NOT NULL,
    description_en TEXT,
    description_fr TEXT,
    difficulty VARCHAR(20) DEFAULT 'beginner' CHECK(difficulty IN ('beginner', 'intermediate', 'advanced')),
    equipment VARCHAR(255),
    image_url VARCHAR(500),
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Training programs table
CREATE TABLE IF NOT EXISTS training_programs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name_en VARCHAR(255) NOT NULL,
    name_fr VARCHAR(255) NOT NULL,
    description_en TEXT,
    description_fr TEXT,
    is_public BOOLEAN DEFAULT 0,
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Program workouts table (specific workout days in a program)
CREATE TABLE IF NOT EXISTS program_workouts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    program_id INTEGER NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    name_fr VARCHAR(255) NOT NULL,
    day_order INTEGER NOT NULL,
    description_en TEXT,
    description_fr TEXT,
    FOREIGN KEY (program_id) REFERENCES training_programs(id) ON DELETE CASCADE
);

-- Program workout exercises (exercises in each program workout)
CREATE TABLE IF NOT EXISTS program_workout_exercises (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    program_workout_id INTEGER NOT NULL,
    exercise_id INTEGER NOT NULL,
    sets_target INTEGER DEFAULT 3,
    reps_target INTEGER DEFAULT 10,
    weight_target REAL,
    rest_seconds INTEGER DEFAULT 60,
    exercise_order INTEGER NOT NULL,
    FOREIGN KEY (program_workout_id) REFERENCES program_workouts(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE
);

-- Workout sessions table (recorded training sessions)
CREATE TABLE IF NOT EXISTS workout_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    program_workout_id INTEGER,
    started_at DATETIME NOT NULL,
    completed_at DATETIME,
    duration_seconds INTEGER,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (program_workout_id) REFERENCES program_workouts(id) ON DELETE SET NULL
);

-- Workout session items (sets/reps data per session)
CREATE TABLE IF NOT EXISTS workout_session_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id INTEGER NOT NULL,
    exercise_id INTEGER NOT NULL,
    set_number INTEGER NOT NULL,
    reps INTEGER NOT NULL,
    weight REAL DEFAULT 0,
    rest_seconds INTEGER,
    completed BOOLEAN DEFAULT 1,
    FOREIGN KEY (session_id) REFERENCES workout_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE
);

-- Bodyweight entries table
CREATE TABLE IF NOT EXISTS bodyweight_entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    weight REAL NOT NULL,
    unit VARCHAR(10) DEFAULT 'kg' CHECK(unit IN ('kg', 'lbs')),
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Personal records table
CREATE TABLE IF NOT EXISTS personal_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    exercise_id INTEGER NOT NULL,
    weight REAL NOT NULL,
    reps INTEGER NOT NULL,
    achieved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE
);

-- User programs table (user's custom or assigned programs)
CREATE TABLE IF NOT EXISTS user_programs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    program_id INTEGER NOT NULL,
    is_active BOOLEAN DEFAULT 1,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES training_programs(id) ON DELETE CASCADE
);

-- Indexes for better performance
CREATE INDEX IF NOT EXISTS idx_exercises_category ON exercises(category_id);
CREATE INDEX IF NOT EXISTS idx_exercises_active ON exercises(is_active);
CREATE INDEX IF NOT EXISTS idx_workout_sessions_user ON workout_sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_workout_sessions_started ON workout_sessions(started_at);
CREATE INDEX IF NOT EXISTS idx_session_items_session ON workout_session_items(session_id);
CREATE INDEX IF NOT EXISTS idx_bodyweight_user ON bodyweight_entries(user_id);
CREATE INDEX IF NOT EXISTS idx_bodyweight_recorded ON bodyweight_entries(recorded_at);
CREATE INDEX IF NOT EXISTS idx_personal_records_user ON personal_records(user_id);
CREATE INDEX IF NOT EXISTS idx_user_programs_user ON user_programs(user_id);