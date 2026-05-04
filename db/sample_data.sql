-- Sample data for GAINZ database
-- Run this after creating the schema

USE gainz;

-- Insert sample exercises
INSERT INTO exercises (name, description, category, muscle_group, instructions, difficulty) VALUES
('Bench Press', 'A compound exercise that targets the chest, shoulders, and triceps.', 'Chest', 'Pectorals', 'Lie on bench, grip barbell shoulder-width, lower to chest, press up.', 3),
('Squat', 'A fundamental lower body exercise targeting quads, glutes, and hamstrings.', 'Legs', 'Quadriceps', 'Stand with feet shoulder-width, lower by bending knees, return to standing.', 3),
('Deadlift', 'A compound movement that works the entire posterior chain.', 'Back', 'Hamstrings', 'Stand over barbell, grip with hands outside legs, lift by extending hips and knees.', 4),
('Overhead Press', 'A shoulder exercise that also engages the core and triceps.', 'Shoulders', 'Deltoids', 'Stand with barbell at shoulder height, press overhead until arms are extended.', 3),
('Pull-ups', 'A bodyweight exercise targeting the back and biceps.', 'Back', 'Lats', 'Hang from bar with palms facing away, pull body up until chin clears bar.', 4),
('Bicep Curls', 'An isolation exercise for the biceps.', 'Arms', 'Biceps', 'Stand with dumbbells, curl weights up by bending elbows, lower slowly.', 2),
('Tricep Dips', 'A bodyweight exercise targeting the triceps.', 'Arms', 'Triceps', 'Sit on edge of bench, lower body by bending elbows, push back up.', 2),
('Lunges', 'A unilateral leg exercise that improves balance and coordination.', 'Legs', 'Quadriceps', 'Step forward with one leg, lower until both knees are bent, push back to start.', 2),
('Plank', 'An isometric core exercise that builds stability.', 'Core', 'Abs', 'Hold body in straight line from head to heels, supported on forearms and toes.', 2),
('Burpees', 'A full-body exercise combining squat, plank, and jump.', 'Full Body', 'Multiple', 'Squat down, kick feet back to plank, do push-up, jump feet forward, jump up.', 4);

-- Insert sample user (password: 'password123' - hashed)
INSERT INTO users (email, password, first_name, last_name, age, language) VALUES
('demo@gainz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo', 'User', 25, 'en');

-- Insert sample workout
INSERT INTO workouts (user_id, date, name, notes, duration, bodyweight) VALUES
(1, CURDATE(), 'Upper Body Day', 'Focused on chest and back', 45, 75.5);

-- Insert workout exercises
INSERT INTO workout_exercises (workout_id, exercise_id, sets, notes) VALUES
(1, 1, '[{"weight": 80, "reps": 8}, {"weight": 85, "reps": 6}, {"weight": 80, "reps": 8}]', 'Good form throughout'),
(1, 4, '[{"weight": 50, "reps": 10}, {"weight": 55, "reps": 8}, {"weight": 50, "reps": 10}]', 'Shoulders felt strong'),
(1, 5, '[{"weight": 0, "reps": 6}, {"weight": 0, "reps": 5}, {"weight": 0, "reps": 4}]', 'Assisted pull-ups');

-- Insert sample training program
INSERT INTO training_programs (name, description, duration_weeks, difficulty_level, created_by) VALUES
('Beginner Strength Program', 'A 4-week program for beginners focusing on compound movements', 4, 2, 1);

-- Insert program exercises
INSERT INTO program_exercises (program_id, exercise_id, week, day, sets, reps, notes) VALUES
(1, 1, 1, 1, 3, 10, 'Light weight, focus on form'),
(1, 2, 1, 1, 3, 12, 'Bodyweight or light dumbbells'),
(1, 4, 1, 2, 3, 10, 'Use dumbbells if no barbell'),
(1, 5, 1, 2, 3, 8, 'Use assisted if needed'),
(1, 1, 2, 1, 4, 8, 'Increase weight slightly'),
(1, 2, 2, 1, 4, 10, 'Add weight if comfortable'),
(1, 4, 2, 2, 4, 8, 'Progressive overload'),
(1, 5, 2, 2, 4, 6, 'Aim for unassisted if possible');