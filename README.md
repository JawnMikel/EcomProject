# GAINZ - Fitness Tracking Application

GAINZ is a web-based fitness tracking application designed to help users plan, record, and analyze their workouts.

## 🚀 Quick Setup (Windows)

### Prerequisites
- **PHP 7.4+ or 8.0+**: Download from [windows.php.net](https://windows.php.net/download/)
- **MySQL/MariaDB 5.7+**: Download from [mysql.com](https://dev.mysql.com/downloads/mysql/) or [mariadb.org](https://mariadb.org/download/)
- **Web Browser**: Chrome, Firefox, or Edge

### Automated Setup
1. **Run the setup script**:
   ```bash
   setup.bat
   ```
   This will install Composer and PHP dependencies automatically.

2. **Configure Database**:
   - Open `.env` file
   - Set your database credentials:
   ```env
   DB_HOST=localhost
   DB_NAME=gainz
   DB_USER=root
   DB_PASSWORD=your_password
   JWT_SECRET=your_super_secret_jwt_key_here
   ```

3. **Create Database**:
   ```bash
   mysql -u root -p < db/schema.sql
   mysql -u root -p < db/sample_data.sql
   ```

4. **Start the Application**:
   ```bash
   composer start
   ```

5. **Open in Browser**:
   - Frontend: http://localhost:8000
   - Demo login: demo@gainz.com / password123

## 📱 Frontend Features

✅ **Modern UI**: Bootstrap-based responsive design
✅ **User Authentication**: Register/login with secure JWT tokens
✅ **Exercise Library**: Browse 10+ sample exercises with categories
✅ **Workout Creation**: Build custom workouts with multiple exercises
✅ **Progress Tracking**: View workout history and analytics
✅ **Mobile Friendly**: Works perfectly on phones and tablets

## 🔧 Manual Setup (Alternative)

If automated setup doesn't work:

1. **Install PHP** and add to PATH
2. **Install Composer**: https://getcomposer.org/download/
3. **Run**: `composer install`
4. **Configure**: Copy `.env.example` to `.env` and edit
5. **Database**: Create MySQL database and run SQL files
6. **Start**: `composer start`

## Project Structure

```
├── public/              # Web entry point
│   └── index.php       # Main application entry point
├── src/
│   ├── Controllers/    # Request handlers
│   ├── Models/         # Data models
│   └── Middleware/     # Custom middleware
├── db/                 # Database files
│   └── schema.sql      # Database schema
├── config/             # Configuration files
├── routes.php          # Application routes
├── composer.json       # PHP dependencies
└── .env               # Environment configuration
```

## Quick Start

### 1. Install Dependencies
```bash
composer install
```

### 2. Setup Environment
```bash
# Copy environment file
copy .env.example .env

# Edit .env with your database credentials
# Required: DB_HOST, DB_NAME, DB_USER, DB_PASSWORD, JWT_SECRET
```

### 3. Setup Database
```bash
# Create database
mysql -u root -p < db/schema.sql

# Add sample data (optional)
mysql -u root -p < db/sample_data.sql
```

### 4. Start the Application
```bash
# Start development server
composer start

# Application will be available at:
# Frontend: http://localhost:8000
# API: http://localhost:8000/api/
```

### 5. Demo Account
- **Email**: demo@gainz.com
- **Password**: password123

## Frontend Features

✅ **Responsive Design**: Works on desktop and mobile
✅ **User Authentication**: Register/login with JWT tokens
✅ **Exercise Library**: Browse exercises with categories and difficulty
✅ **Workout Logging**: Create and track workouts with sets/reps
✅ **Real-time Updates**: Dynamic content loading
✅ **Bootstrap UI**: Modern, clean interface

## API Endpoints

### Authentication
- `POST /register` - Create new user account
- `POST /login` - Login and get JWT token
- `POST /logout` - Logout user

### Workouts
- `GET /workouts` - List user's workouts
- `POST /workouts` - Create new workout
- `GET /workouts/{id}` - Get workout details

### Exercises
- `GET /exercises` - List all exercises
- `GET /exercises/{id}` - Get exercise details

### Admin (requires admin role)
- `POST /admin/exercises` - Create exercise
- `POST /admin/programs` - Create training program

## Development

### Running Tests
```bash
composer test
```

### Code Linting
```bash
composer lint
```

## Tech Stack

- **Framework**: Slim 4 (PHP microframework)
- **Authentication**: JWT (Firebase JWT)
- **Database**: MySQL/MariaDB
- **Validation**: Respect/Validation
- **Environment**: PHP dotenv

## Configuration

All configuration is managed through the `.env` file:
- `APP_ENV` - Application environment (development/production)
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` - Database connection
- `JWT_SECRET` - Secret key for JWT tokens
- `MIN_USER_AGE` - Minimum age for registration (default: 16)
- `SUPPORTED_LANGUAGES` - Comma-separated list of supported languages

## Roadmap

- [ ] Complete database integration
- [ ] Implement user authentication with 2FA
- [ ] Build frontend UI (React/Vue)
- [ ] Add workout analytics dashboard
- [ ] Mobile app support
- [ ] Social features (optional)

## License

MIT License - See LICENSE file for details

## Support

For issues, feature requests, or contributions, please open an issue on GitHub.
