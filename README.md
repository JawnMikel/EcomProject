# GAINZ - Fitness Tracking Application

GAINZ is a web-based fitness tracking application built with PHP/Slim that helps users plan, record, and analyze their workouts.

## Quick Setup (XAMPP)

### Prerequisites
- **PHP 8.1+** with MySQL extension
- **MySQL/MariaDB** (via XAMPP)
- **Web Browser**: Chrome, Firefox, or Edge

### Setup
1. **Configure `.env`** file:
   ```env
   DB_HOST=localhost
   DB_NAME=gainz
   DB_USER=root
   DB_PASSWORD=
   JWT_SECRET=your_super_secret_jwt_key_here
   ```

2. **Create Database** in phpMyAdmin:
   - Create database named `gainz`
   - Import `database/schema.sql`

3. **Start Server**:
   - Start Apache in XAMPP
   - Access at: http://localhost/EcomProject/public

## Features

- **Dashboard** - View workout stats, progress, and recent activity
- **Workouts** - Start and track workout sessions with exercises/sets/reps
- **Programs** - Create and manage training routines
- **Body Weight** - Track weight over time
- **Calendar** - View workout schedule
- **Analytics** - Review training trends and progress
- **Profile** - Manage account and fitness info
- **2FA** - Two-factor authentication via email

## Project Structure

```
├── app/
│   ├── Controllers/    # Request handlers (Auth, Dashboard, Workout, etc.)
│   ├── Models/        # Database models (User, Workout, Exercise, etc.)
│   ├── Middleware/    # Auth middleware
│   ├── Services/      # Email, 2FA services
│   └── Views/         # Twig templates
├── public/
│   ├── css/           # gainz-theme.css
│   ├── js/            # JavaScript files
│   ├── locales/       # i18n (en.json, fr.json)
│   └── index.php      # Entry point
├── config/
│   ├── routes.php     # Application routes
│   ├── container.php  # DI container
│   └── settings.php   # App settings
├── database/
│   ├── schema.sql     # Database schema
│   └── add_2fa.sql    # 2FA migration
└── vendor/            # PHP dependencies
```

## Tech Stack

- **Framework**: Slim 4 (PHP)
- **Templating**: Twig
- **Database**: MySQL/MariaDB (PDO)
- **Auth**: Session-based with 2FA
- **CSS**: Custom dark theme (GAINZ aesthetic)
- **i18n**: English & French support

## Configuration

Edit `.env`:
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` - Database
- `JWT_SECRET` - Session secret
- `MIN_USER_AGE` - Minimum registration age (default: 16)
- `EMAIL_*` - SMTP settings for 2FA codes

## Development

```bash
# Install dependencies
composer install

# Start dev server (optional)
php -S localhost:8000 -t public
```

## License

MIT License