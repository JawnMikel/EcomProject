-- GAINZ Database Seed Data
-- Initial data for categories, exercises, and sample programs

-- Insert muscle group categories
INSERT INTO categories (name_en, name_fr, description_en, description_fr) VALUES
('Chest', 'Pectoraux', 'Exercises targeting the pectoral muscles', 'Exercices ciblant les muscles pectoraux'),
('Back', 'Dos', 'Exercises targeting the back muscles', 'Exercices ciblant les muscles du dos'),
('Legs', 'Jambes', 'Exercises targeting the leg muscles', 'Exercices ciblant les muscles des jambes'),
('Shoulders', 'Épaules', 'Exercises targeting the shoulder muscles', 'Exercices ciblant les muscles des épaules'),
('Arms', 'Bras', 'Exercises targeting the arm muscles', 'Exercices ciblant les muscles des bras'),
('Core', 'Abdominaux', 'Exercises targeting the core muscles', 'Exercices ciblant les muscles abdominaux'),
('Cardio', 'Cardio', 'Cardiovascular exercises', 'Exercices cardiovasculaires'),
('Full Body', 'Corps complet', 'Exercises targeting multiple muscle groups', 'Exercices ciblant plusieurs groupes musculaires');

-- Insert exercises
INSERT INTO exercises (category_id, name_en, name_fr, description_en, description_fr, difficulty, equipment) VALUES
-- Chest exercises
(1, 'Bench Press', 'Développé couché', 'Classic barbell bench press for chest development', 'Développé couché classique à la barre pour le développement des pectoraux', 'intermediate', 'Barbell, Bench'),
(1, 'Incline Dumbbell Press', 'Développé incliné aux haltères', 'Targets upper chest with an inclined angle', 'Cible le haut des pectoraux avec un angle incliné', 'intermediate', 'Dumbbells, Incline Bench'),
(1, 'Push-ups', 'Pompes', 'Bodyweight exercise for chest and triceps', 'Exercice au poids du corps pour les pectoraux et triceps', 'beginner', 'None'),
(1, 'Chest Fly', 'Écarté à la poulie', 'Isolation exercise for chest', 'Exercice d''isolation pour les pectoraux', 'beginner', 'Cable Machine'),
(1, 'Dips', 'Dips', 'Bodyweight exercise targeting lower chest', 'Exercice au poids du corps ciblant le bas des pectoraux', 'intermediate', 'Dip Bars'),

-- Back exercises
(2, 'Pull-ups', 'Tractions', 'Classic bodyweight back exercise', 'Exercice classique du dos au poids du corps', 'intermediate', 'Pull-up Bar'),
(2, 'Barbell Row', 'Rowing à la barre', 'Compound exercise for back thickness', 'Exercice composé pour l''épaisseur du dos', 'intermediate', 'Barbell'),
(2, 'Lat Pulldown', 'Tirage vertical', 'Machine exercise for lat development', 'Exercice à la machine pour le développement des grands dorsaux', 'beginner', 'Lat Pulldown Machine'),
(2, 'Seated Cable Row', 'Tirage horizontal', 'Cable exercise for middle back', 'Exercice à la poulie pour le milieu du dos', 'beginner', 'Cable Machine'),
(2, 'Deadlift', 'Soulevé de terre', 'Compound exercise for overall back development', 'Exercice composé pour le développement global du dos', 'advanced', 'Barbell'),

-- Legs exercises
(3, 'Squat', 'Squat', 'King of leg exercises', 'Le roi des exercices de jambes', 'intermediate', 'Barbell, Squat Rack'),
(3, 'Leg Press', 'Presse à cuisses', 'Machine exercise for quadriceps', 'Exercice à la machine pour les quadriceps', 'beginner', 'Leg Press Machine'),
(3, 'Lunges', 'Fentes', 'Unilateral leg exercise', 'Exercice unilatéral pour les jambes', 'beginner', 'Dumbbells'),
(3, 'Leg Curl', 'Leg Curl', 'Isolation exercise for hamstrings', 'Exercice d''isolation pour les ischio-jambiers', 'beginner', 'Leg Curl Machine'),
(3, 'Calf Raise', 'Élévations des mollets', 'Exercise for calf development', 'Exercice pour le développement des mollets', 'beginner', 'Calf Raise Machine'),

-- Shoulder exercises
(4, 'Overhead Press', 'Développé militaire', 'Compound exercise for shoulder development', 'Exercice composé pour le développement des épaules', 'intermediate', 'Barbell'),
(4, 'Lateral Raise', 'Élévations latérales', 'Isolation exercise for side delts', 'Exercice d''isolation pour les deltoïdes latéraux', 'beginner', 'Dumbbells'),
(4, 'Face Pull', 'Face Pull', 'Exercise for rear delts and posture', 'Exercice pour les deltoïdes postérieurs et la posture', 'beginner', 'Cable Machine'),
(4, 'Arnold Press', 'Développé Arnold', 'Rotational shoulder press variation', 'Variante de développé d''épaules avec rotation', 'intermediate', 'Dumbbells'),

-- Arms exercises
(5, 'Bicep Curl', 'Curl biceps', 'Classic bicep isolation exercise', 'Exercice d''isolation classique pour les biceps', 'beginner', 'Dumbbells or Barbell'),
(5, 'Tricep Pushdown', 'Extension triceps à la poulie', 'Cable exercise for triceps', 'Exercice à la poulie pour les triceps', 'beginner', 'Cable Machine'),
(5, 'Hammer Curl', 'Curl marteau', 'Bicep exercise with neutral grip', 'Exercice pour les biceps avec prise neutre', 'beginner', 'Dumbbells'),
(5, 'Skull Crushers', 'Barre au front', 'Lying tricep extension', 'Extension des triceps allongé', 'intermediate', 'Barbell or EZ Bar'),

-- Core exercises
(6, 'Plank', 'Gainage', 'Isometric core exercise', 'Exercice isométrique pour les abdominaux', 'beginner', 'None'),
(6, 'Russian Twist', 'Rotation russe', 'Rotational core exercise', 'Exercice de rotation pour les abdominaux', 'beginner', 'Medicine Ball or Weight'),
(6, 'Leg Raise', 'Élévations de jambes', 'Exercise for lower abs', 'Exercice pour les abdominaux inférieurs', 'intermediate', 'Pull-up Bar or Bench'),
(6, 'Cable Crunch', 'Crunch à la poulie', 'Weighted ab exercise', 'Exercice lesté pour les abdominaux', 'intermediate', 'Cable Machine'),

-- Cardio exercises
(7, 'Treadmill Running', 'Course sur tapis', 'Indoor running on treadmill', 'Course en intérieur sur tapis roulant', 'beginner', 'Treadmill'),
(7, 'Stationary Bike', 'Vélo stationnaire', 'Indoor cycling exercise', 'Exercice de cyclisme en intérieur', 'beginner', 'Exercise Bike'),
(7, 'Rowing Machine', 'Rameur', 'Full body cardio exercise', 'Exercice cardio pour tout le corps', 'intermediate', 'Rowing Machine'),
(7, 'Jump Rope', 'Corde à sauter', 'High intensity cardio', 'Cardio haute intensité', 'beginner', 'Jump Rope');

-- Insert sample training programs
INSERT INTO training_programs (name_en, name_fr, description_en, description_fr, is_public, created_by) VALUES
('Beginner Full Body', 'Débutant - Corps complet', 'A 3-day per week full body program for beginners', 'Un programme de 3 jours par semaine pour débutants', 1, NULL),
('Push Pull Legs (PPL)', 'Push Pull Legs (PPL)', 'Classic 6-day split focusing on push, pull, and leg days', 'Split classique de 6 jours axé sur les jours push, pull et jambes', 1, NULL),
('Upper Lower Split', 'Split Haut/Bas', '4-day split alternating between upper and lower body', 'Split de 4 jours alternant entre le haut et le bas du corps', 1, NULL);

-- Program workouts for Beginner Full Body (Program 1)
INSERT INTO program_workouts (program_id, name_en, name_fr, day_order, description_en, description_fr) VALUES
(1, 'Day A - Full Body', 'Jour A - Corps complet', 1, 'First full body workout', 'Premier entraînement corps complet'),
(1, 'Day B - Full Body', 'Jour B - Corps complet', 2, 'Second full body workout', 'Deuxième entraînement corps complet'),
(1, 'Day C - Full Body', 'Jour C - Corps complet', 3, 'Third full body workout', 'Troisième entraînement corps complet');

-- Exercises for Day A
INSERT INTO program_workout_exercises (program_workout_id, exercise_id, sets_target, reps_target, rest_seconds, exercise_order) VALUES
(1, 1, 3, 8, 120, 1),  -- Bench Press
(1, 6, 3, 8, 120, 2),  -- Pull-ups (assisted if needed)
(1, 3, 3, 10, 90, 3),  -- Squat
(1, 16, 3, 10, 90, 4), -- Overhead Press
(1, 21, 3, 12, 60, 5), -- Plank (hold for time)
(1, 30, 10, 0, 60, 6); -- Jump Rope (minutes)

-- Exercises for Day B
INSERT INTO program_workout_exercises (program_workout_id, exercise_id, sets_target, reps_target, rest_seconds, exercise_order) VALUES
(2, 2, 3, 10, 90, 1),  -- Incline Dumbbell Press
(2, 8, 3, 10, 90, 2),  -- Seated Cable Row
(2, 4, 3, 12, 90, 3),  -- Leg Press
(2, 17, 3, 12, 90, 4), -- Lateral Raise
(2, 22, 3, 15, 60, 5), -- Russian Twist
(2, 31, 15, 0, 60, 6); -- Stationary Bike (minutes)

-- Exercises for Day C
INSERT INTO program_workout_exercises (program_workout_id, exercise_id, sets_target, reps_target, rest_seconds, exercise_order) VALUES
(3, 3, 3, 8, 120, 1),  -- Squat
(3, 9, 3, 8, 120, 2),  -- Deadlift
(3, 3, 3, 10, 90, 3),  -- Push-ups
(3, 18, 3, 12, 90, 4), -- Face Pull
(3, 23, 3, 12, 60, 5), -- Leg Raise
(3, 32, 15, 0, 60, 6); -- Rowing Machine (minutes)

-- Program workouts for PPL (Program 2)
INSERT INTO program_workouts (program_id, name_en, name_fr, day_order, description_en, description_fr) VALUES
(2, 'Push Day', 'Jour Push', 1, 'Chest, Shoulders, Triceps', 'Pectoraux, Épaules, Triceps'),
(2, 'Pull Day', 'Jour Pull', 2, 'Back, Biceps', 'Dos, Biceps'),
(2, 'Leg Day', 'Jour Jambes', 3, 'Quads, Hamstrings, Calves', 'Quadriceps, Ischios, Mollets'),
(2, 'Push Day 2', 'Jour Push 2', 4, 'Second push session', 'Deuxième séance push'),
(2, 'Pull Day 2', 'Jour Pull 2', 5, 'Second pull session', 'Deuxième séance pull'),
(2, 'Leg Day 2', 'Jour Jambes 2', 6, 'Second leg session', 'Deuxième séance jambes');

-- Push Day 1 exercises
INSERT INTO program_workout_exercises (program_workout_id, exercise_id, sets_target, reps_target, rest_seconds, exercise_order) VALUES
(4, 1, 4, 6, 180, 1),  -- Bench Press
(4, 2, 3, 8, 120, 2),  -- Incline Dumbbell Press
(4, 16, 4, 8, 120, 3), -- Overhead Press
(4, 17, 3, 12, 90, 4), -- Lateral Raise
(4, 19, 3, 10, 90, 5), -- Bicep Curl
(4, 20, 3, 10, 90, 6); -- Tricep Pushdown

-- Pull Day 1 exercises
INSERT INTO program_workout_exercises (program_workout_id, exercise_id, sets_target, reps_target, rest_seconds, exercise_order) VALUES
(5, 6, 4, 8, 120, 1),  -- Pull-ups
(5, 7, 4, 8, 120, 2),  -- Barbell Row
(5, 8, 3, 10, 90, 3),  -- Seated Cable Row
(5, 18, 3, 15, 90, 4), -- Face Pull
(5, 24, 3, 10, 90, 5), -- Skull Crushers
(5, 22, 3, 15, 60, 6); -- Russian Twist

-- Leg Day 1 exercises
INSERT INTO program_workout_exercises (program_workout_id, exercise_id, sets_target, reps_target, rest_seconds, exercise_order) VALUES
(6, 3, 4, 6, 180, 1),  -- Squat
(6, 9, 3, 5, 180, 2),  -- Deadlift
(6, 10, 3, 10, 120, 3), -- Leg Press
(6, 11, 3, 10, 90, 4), -- Lunges
(6, 12, 3, 12, 90, 5), -- Leg Curl
(6, 13, 4, 15, 60, 6); -- Calf Raise

-- Push Day 2 exercises
INSERT INTO program_workout_exercises (program_workout_id, exercise_id, sets_target, reps_target, rest_seconds, exercise_order) VALUES
(7, 5, 3, 10, 120, 1), -- Dips
(7, 2, 3, 10, 120, 2), -- Incline Dumbbell Press
(7, 19, 4, 12, 90, 3), -- Bicep Curl
(7, 17, 4, 15, 90, 4), -- Lateral Raise
(7, 20, 3, 12, 90, 5), -- Tricep Pushdown
(7, 21, 3, 30, 60, 6); -- Plank (seconds)

-- Pull Day 2 exercises
INSERT INTO program_workout_exercises (program_workout_id, exercise_id, sets_target, reps_target, rest_seconds, exercise_order) VALUES
(8, 8, 4, 10, 90, 1), -- Seated Cable Row
(8, 7, 3, 10, 120, 2), -- Barbell Row
(8, 18, 4, 15, 90, 3), -- Face Pull
(8, 24, 3, 12, 90, 4), -- Skull Crushers
(8, 22, 3, 20, 60, 5), -- Russian Twist
(8, 32, 15, 0, 60, 6); -- Rowing Machine (minutes)

-- Leg Day 2 exercises
INSERT INTO program_workout_exercises (program_workout_id, exercise_id, sets_target, reps_target, rest_seconds, exercise_order) VALUES
(9, 10, 4, 10, 120, 1), -- Leg Press
(9, 11, 3, 12, 90, 2),  -- Lunges
(9, 12, 4, 12, 90, 3),  -- Leg Curl
(9, 13, 4, 15, 60, 4),  -- Calf Raise
(9, 23, 3, 12, 60, 5),  -- Leg Raise
(9, 30, 10, 0, 60, 6);  -- Jump Rope (minutes)

-- Upper Lower Split (Program 3)
INSERT INTO program_workouts (program_id, name_en, name_fr, day_order, description_en, description_fr) VALUES
(3, 'Upper Body A', 'Haut du corps A', 1, 'First upper body session', 'Première séance haut du corps'),
(3, 'Lower Body A', 'Bas du corps A', 2, 'First lower body session', 'Première séance bas du corps'),
(3, 'Rest', 'Repos', 3, 'Rest day', 'Jour de repos'),
(3, 'Upper Body B', 'Haut du corps B', 4, 'Second upper body session', 'Deuxième séance haut du corps'),
(3, 'Lower Body B', 'Bas du corps B', 5, 'Second lower body session', 'Deuxième séance bas du corps'),
(3, 'Rest', 'Repos', 6, 'Rest day', 'Jour de repos'),
(3, 'Rest', 'Repos', 7, 'Rest day', 'Jour de repos');

-- Upper Body A
INSERT INTO program_workout_exercises (program_workout_id, exercise_id, sets_target, reps_target, rest_seconds, exercise_order) VALUES
(10, 1, 4, 8, 120, 1),  -- Bench Press
(10, 6, 4, 8, 120, 2),  -- Pull-ups
(10, 16, 3, 10, 90, 3), -- Overhead Press
(10, 7, 3, 10, 90, 4),  -- Barbell Row
(10, 17, 3, 12, 90, 5), -- Lateral Raise
(10, 19, 3, 12, 90, 6); -- Bicep Curl

-- Lower Body A
INSERT INTO program_workout_exercises (program_workout_id, exercise_id, sets_target, reps_target, rest_seconds, exercise_order) VALUES
(11, 3, 4, 6, 180, 1),  -- Squat
(11, 9, 3, 5, 180, 2),  -- Deadlift
(11, 10, 3, 10, 120, 3), -- Leg Press
(11, 12, 3, 12, 90, 4),  -- Leg Curl
(11, 13, 4, 15, 60, 5),  -- Calf Raise
(11, 21, 3, 30, 60, 6);  -- Plank (seconds)

-- Upper Body B
INSERT INTO program_workout_exercises (program_workout_id, exercise_id, sets_target, reps_target, rest_seconds, exercise_order) VALUES
(13, 2, 4, 8, 120, 1),  -- Incline Dumbbell Press
(13, 8, 4, 10, 90, 2),  -- Seated Cable Row
(13, 5, 3, 10, 120, 3), -- Dips
(13, 18, 3, 15, 90, 4), -- Face Pull
(13, 20, 3, 12, 90, 5), -- Tricep Pushdown
(13, 24, 3, 12, 90, 6); -- Skull Crushers

-- Lower Body B
INSERT INTO program_workout_exercises (program_workout_id, exercise_id, sets_target, reps_target, rest_seconds, exercise_order) VALUES
(14, 3, 3, 8, 180, 1),  -- Squat
(14, 11, 3, 12, 90, 2),  -- Lunges
(14, 10, 3, 10, 120, 3), -- Leg Press
(14, 12, 3, 12, 90, 4),  -- Leg Curl
(14, 23, 3, 12, 60, 5),  -- Leg Raise
(14, 31, 15, 0, 60, 6); -- Stationary Bike (minutes)