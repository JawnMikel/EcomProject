// GAINZ Frontend Application
class GainzApp {
    constructor() {
        this.apiBase = 'http://localhost:8000';
        this.token = localStorage.getItem('gainz_token');
        this.currentUser = null;
        this.exercises = [];
        this.workouts = [];
        this.exerciseCounter = 0;

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.checkAuthStatus();
        this.showSection('home');
    }

    setupEventListeners() {
        // Forms
        document.getElementById('loginForm').addEventListener('submit', (e) => this.handleLogin(e));
        document.getElementById('registerForm').addEventListener('submit', (e) => this.handleRegister(e));
        document.getElementById('workoutForm').addEventListener('submit', (e) => this.handleCreateWorkout(e));

        // Initialize workout date to today
        document.getElementById('workoutDate').valueAsDate = new Date();
    }

    checkAuthStatus() {
        if (this.token) {
            this.updateAuthUI(true);
            this.loadUserData();
        } else {
            this.updateAuthUI(false);
        }
    }

    updateAuthUI(isLoggedIn) {
        const authNav = document.getElementById('authNav');
        if (isLoggedIn) {
            authNav.innerHTML = `
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="app.logout()">Logout</a>
                </li>
            `;
        } else {
            authNav.innerHTML = `
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="app.showSection('login')">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="app.showSection('register')">Register</a>
                </li>
            `;
        }
    }

    showSection(sectionName) {
        // Hide all sections
        document.querySelectorAll('.section').forEach(section => {
            section.classList.remove('active');
        });

        // Show selected section
        const section = document.getElementById(sectionName + 'Section');
        if (section) {
            section.classList.add('active');
        }

        // Load data for specific sections
        if (sectionName === 'exercises') {
            this.loadExercises();
        } else if (sectionName === 'workouts') {
            this.loadWorkouts();
        }
    }

    async handleLogin(e) {
        e.preventDefault();

        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;

        try {
            const response = await fetch(`${this.apiBase}/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (response.ok) {
                this.token = data.token;
                localStorage.setItem('gainz_token', this.token);
                this.updateAuthUI(true);
                this.showAlert('Login successful!', 'success');
                this.showSection('home');
                this.loadUserData();
            } else {
                this.showAlert(data.message || 'Login failed', 'danger');
            }
        } catch (error) {
            this.showAlert('Network error. Please try again.', 'danger');
        }
    }

    async handleRegister(e) {
        e.preventDefault();

        const firstName = document.getElementById('firstName').value;
        const lastName = document.getElementById('lastName').value;
        const email = document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;
        const age = document.getElementById('age').value;
        const language = document.getElementById('language').value;

        try {
            const response = await fetch(`${this.apiBase}/register`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    first_name: firstName,
                    last_name: lastName,
                    email,
                    password,
                    age: parseInt(age),
                    language
                })
            });

            const data = await response.json();

            if (response.ok) {
                this.showAlert('Registration successful! Please check your email.', 'success');
                this.showSection('login');
            } else {
                this.showAlert(data.message || 'Registration failed', 'danger');
            }
        } catch (error) {
            this.showAlert('Network error. Please try again.', 'danger');
        }
    }

    async loadExercises() {
        try {
            const response = await fetch(`${this.apiBase}/exercises`);
            const data = await response.json();

            if (response.ok) {
                this.exercises = data.exercises || [];
                this.renderExercises();
            } else {
                this.showAlert('Failed to load exercises', 'danger');
            }
        } catch (error) {
            this.showAlert('Network error loading exercises', 'danger');
        }
    }

    renderExercises() {
        const container = document.getElementById('exercisesContainer');
        container.innerHTML = '';

        if (this.exercises.length === 0) {
            container.innerHTML = '<div class="col-12"><div class="alert alert-info">No exercises found.</div></div>';
            return;
        }

        this.exercises.forEach(exercise => {
            const difficultyClass = `difficulty-${exercise.difficulty || 1}`;
            const card = `
                <div class="col-md-6 col-lg-4">
                    <div class="card exercise-card">
                        <div class="card-body">
                            <h5 class="card-title">${exercise.name}</h5>
                            <p class="card-text">${exercise.description || 'No description available.'}</p>
                            <div class="mb-2">
                                <span class="badge ${difficultyClass}">Difficulty: ${exercise.difficulty || 1}/5</span>
                            </div>
                            <div class="mb-2">
                                <span class="muscle-group">${exercise.muscle_group || 'General'}</span>
                                <span class="muscle-group">${exercise.category || 'Exercise'}</span>
                            </div>
                            <button class="btn btn-primary btn-sm" onclick="app.addExerciseToWorkout(${exercise.id}, '${exercise.name}')">
                                <i class="fas fa-plus"></i> Add to Workout
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += card;
        });
    }

    async loadWorkouts() {
        if (!this.token) {
            this.showAlert('Please login to view workouts', 'warning');
            return;
        }

        try {
            const response = await fetch(`${this.apiBase}/workouts`, {
                headers: {
                    'Authorization': `Bearer ${this.token}`
                }
            });

            const data = await response.json();

            if (response.ok) {
                this.workouts = data.workouts || [];
                this.renderWorkouts();
            } else {
                this.showAlert('Failed to load workouts', 'danger');
            }
        } catch (error) {
            this.showAlert('Network error loading workouts', 'danger');
        }
    }

    renderWorkouts() {
        const container = document.getElementById('workoutsContainer');
        container.innerHTML = '';

        if (this.workouts.length === 0) {
            container.innerHTML = '<div class="col-12"><div class="alert alert-info">No workouts found. Create your first workout!</div></div>';
            return;
        }

        this.workouts.forEach(workout => {
            const card = `
                <div class="col-12">
                    <div class="card workout-card">
                        <div class="card-header">
                            <h6 class="mb-0">${workout.name} - ${new Date(workout.date).toLocaleDateString()}</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">${workout.notes || 'No notes'}</p>
                            <small class="text-muted">Duration: ${workout.duration || 0} minutes</small>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += card;
        });
    }

    addExercise() {
        this.exerciseCounter++;
        const exerciseList = document.getElementById('exerciseList');

        const exerciseDiv = document.createElement('div');
        exerciseDiv.className = 'exercise-input position-relative';
        exerciseDiv.id = `exercise-${this.exerciseCounter}`;

        exerciseDiv.innerHTML = `
            <button type="button" class="remove-exercise-btn" onclick="app.removeExercise(${this.exerciseCounter})">
                <i class="fas fa-times"></i>
            </button>
            <div class="mb-2">
                <input type="text" class="form-control" placeholder="Exercise name" id="exercise-name-${this.exerciseCounter}" required>
            </div>
            <div id="sets-${this.exerciseCounter}">
                <div class="set-input">
                    <input type="number" class="form-control" placeholder="Weight (kg)" id="weight-${this.exerciseCounter}-1">
                    <input type="number" class="form-control" placeholder="Reps" id="reps-${this.exerciseCounter}-1">
                    <button type="button" class="remove-set-btn" onclick="app.removeSet(${this.exerciseCounter}, 1)">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="app.addSet(${this.exerciseCounter})">
                <i class="fas fa-plus"></i> Add Set
            </button>
        `;

        exerciseList.appendChild(exerciseDiv);
    }

    addExerciseToWorkout(exerciseId, exerciseName) {
        this.showSection('newWorkout');
        // This would populate the workout form with the selected exercise
        // For now, just show the new workout section
    }

    addSet(exerciseId) {
        const setsContainer = document.getElementById(`sets-${exerciseId}`);
        const setCount = setsContainer.children.length + 1;

        const setDiv = document.createElement('div');
        setDiv.className = 'set-input';
        setDiv.innerHTML = `
            <input type="number" class="form-control" placeholder="Weight (kg)" id="weight-${exerciseId}-${setCount}">
            <input type="number" class="form-control" placeholder="Reps" id="reps-${exerciseId}-${setCount}">
            <button type="button" class="remove-set-btn" onclick="app.removeSet(${exerciseId}, ${setCount})">
                <i class="fas fa-minus"></i>
            </button>
        `;

        setsContainer.appendChild(setDiv);
    }

    removeSet(exerciseId, setNumber) {
        const setElement = document.getElementById(`weight-${exerciseId}-${setNumber}`).parentElement;
        if (setElement) {
            setElement.remove();
        }
    }

    removeExercise(exerciseId) {
        const exerciseElement = document.getElementById(`exercise-${exerciseId}`);
        if (exerciseElement) {
            exerciseElement.remove();
        }
    }

    async handleCreateWorkout(e) {
        e.preventDefault();

        if (!this.token) {
            this.showAlert('Please login to create workouts', 'warning');
            return;
        }

        const workoutName = document.getElementById('workoutName').value;
        const workoutDate = document.getElementById('workoutDate').value;
        const workoutNotes = document.getElementById('workoutNotes').value;
        const bodyweight = document.getElementById('bodyweight').value;

        // Collect exercises data
        const exercises = [];
        const exerciseElements = document.querySelectorAll('[id^="exercise-"]');

        exerciseElements.forEach(exerciseEl => {
            const exerciseId = exerciseEl.id.split('-')[1];
            const exerciseName = document.getElementById(`exercise-name-${exerciseId}`).value;

            if (exerciseName.trim()) {
                const sets = [];
                const setElements = exerciseEl.querySelectorAll('.set-input');

                setElements.forEach((setEl, index) => {
                    const weightInput = setEl.querySelector(`#weight-${exerciseId}-${index + 1}`);
                    const repsInput = setEl.querySelector(`#reps-${exerciseId}-${index + 1}`);

                    if (weightInput && repsInput) {
                        const weight = parseFloat(weightInput.value);
                        const reps = parseInt(repsInput.value);

                        if (!isNaN(weight) && !isNaN(reps)) {
                            sets.push({ weight, reps });
                        }
                    }
                });

                if (sets.length > 0) {
                    exercises.push({
                        name: exerciseName,
                        sets: sets
                    });
                }
            }
        });

        try {
            const response = await fetch(`${this.apiBase}/workouts`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.token}`
                },
                body: JSON.stringify({
                    name: workoutName,
                    date: workoutDate,
                    notes: workoutNotes,
                    bodyweight: bodyweight ? parseFloat(bodyweight) : null,
                    exercises: exercises
                })
            });

            const data = await response.json();

            if (response.ok) {
                this.showAlert('Workout created successfully!', 'success');
                this.showSection('workouts');
                this.loadWorkouts();
            } else {
                this.showAlert(data.message || 'Failed to create workout', 'danger');
            }
        } catch (error) {
            this.showAlert('Network error. Please try again.', 'danger');
        }
    }

    logout() {
        this.token = null;
        localStorage.removeItem('gainz_token');
        this.currentUser = null;
        this.updateAuthUI(false);
        this.showSection('home');
        this.showAlert('Logged out successfully', 'info');
    }

    loadUserData() {
        // This would load user profile data
        // For now, just ensure we're authenticated
    }

    showAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alertContainer');
        const alertId = 'alert-' + Date.now();

        const alert = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        alertContainer.innerHTML += alert;

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alertElement = document.getElementById(alertId);
            if (alertElement) {
                alertElement.remove();
            }
        }, 5000);
    }
}

// Initialize the app when DOM is loaded
let app;
document.addEventListener('DOMContentLoaded', () => {
    app = new GainzApp();
    window.app = app;
    window.showSection = (sectionName) => {
        if (window.app) {
            window.app.showSection(sectionName);
        }
    };
});