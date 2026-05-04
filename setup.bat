@echo off
REM GAINZ Setup Script for Windows
echo Setting up GAINZ Fitness Application...

REM Check if PHP is installed
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo PHP is not installed. Please install PHP 7.4+ or 8.0+ from https://windows.php.net/download/
    echo After installing PHP, add it to your PATH and run this script again.
    pause
    exit /b 1
)

REM Check if Composer is installed
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Installing Composer...
    powershell -Command "& {Invoke-WebRequest -Uri 'https://getcomposer.org/installer' -OutFile 'composer-setup.php'}"
    php composer-setup.php --quiet
    move composer.phar composer.bat >nul 2>&1
    del composer-setup.php
)

REM Install PHP dependencies
echo Installing PHP dependencies...
composer install

REM Check if .env exists
if not exist .env (
    echo Creating .env file from template...
    copy .env.example .env 2>nul
    if not exist .env (
        echo Please create a .env file with your database configuration
        echo Required: DB_HOST, DB_NAME, DB_USER, DB_PASSWORD, JWT_SECRET
    )
)

REM Check MySQL
mysql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo MySQL/MariaDB not found. Please install MySQL 5.7+ or MariaDB
    echo After installation, create a database and run:
    echo mysql -u root -p ^< db\schema.sql
    echo mysql -u root -p ^< db\sample_data.sql
)

echo Setup complete! Run 'composer start' to start the application.
echo Frontend will be available at: http://localhost:8000
pause