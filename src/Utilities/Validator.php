<?php

namespace App\Utilities;

class Validator
{
    /**
     * Validate email format
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate password strength
     * - At least 8 characters
     * - At least one uppercase letter
     * - At least one lowercase letter
     * - At least one number
     */
    public static function isValidPassword(string $password): bool
    {
        // At least 8 characters, one uppercase, one lowercase, one number
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password) === 1;
    }

    /**
     * Validate username
     * - 3-50 characters
     * - Only alphanumeric and underscore
     */
    public static function isValidUsername(string $username): bool
    {
        return preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username) === 1;
    }

    /**
     * Validate date of birth (user must be at least 16 years old)
     */
    public static function isValidDateOfBirth(string $dateOfBirth): bool
    {
        $birthDate = \DateTime::createFromFormat('Y-m-d', $dateOfBirth);
        
        if (!$birthDate || $birthDate->format('Y-m-d') !== $dateOfBirth) {
            return false;
        }

        $today = new \DateTime();
        $age = $today->diff($birthDate)->y;

        return $age >= 16 && $age <= 120;
    }

    /**
     * Validate a non-empty string
     */
    public static function isNonEmptyString(?string $value): bool
    {
        return $value !== null && trim($value) !== '';
    }

    /**
     * Sanitize string for XSS prevention
     */
    public static function sanitizeString(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate integer range
     */
    public static function isValidIntRange(?int $value, int $min, int $max): bool
    {
        return $value !== null && $value >= $min && $value <= $max;
    }

    /**
     * Validate positive number
     */
    public static function isPositiveNumber(?float $value): bool
    {
        return $value !== null && $value > 0;
    }

    /**
     * Validate non-negative number
     */
    public static function isNonNegativeNumber(?float $value): bool
    {
        return $value !== null && $value >= 0;
    }

    /**
     * Validate 2FA code (6 digits)
     */
    public static function isValid2FACode(string $code): bool
    {
        return preg_match('/^\d{6}$/', $code) === 1;
    }

    /**
     * Get validation errors for user registration
     */
    public static function validateRegistration(array $data): array
    {
        $errors = [];

        if (!self::isValidEmail($data['email'] ?? '')) {
            $errors['email'] = 'auth.invalid_email';
        }

        if (!self::isValidUsername($data['username'] ?? '')) {
            $errors['username'] = 'auth.invalid_username';
        }

        if (!self::isValidPassword($data['password'] ?? '')) {
            $errors['password'] = 'auth.weak_password';
        }

        if (($data['password'] ?? '') !== ($data['confirm_password'] ?? '')) {
            $errors['confirm_password'] = 'auth.password_mismatch';
        }

        if (!self::isValidDateOfBirth($data['date_of_birth'] ?? '')) {
            $errors['date_of_birth'] = 'auth.must_be_16';
        }

        return $errors;
    }

    /**
     * Get validation errors for exercise creation
     */
    public static function validateExercise(array $data): array
    {
        $errors = [];

        if (!self::isNonEmptyString($data['name_en'] ?? null)) {
            $errors['name_en'] = 'exercises.name_required_en';
        }

        if (!self::isNonEmptyString($data['name_fr'] ?? null)) {
            $errors['name_fr'] = 'exercises.name_required_fr';
        }

        $validDifficulties = ['beginner', 'intermediate', 'advanced'];
        if (!in_array($data['difficulty'] ?? '', $validDifficulties)) {
            $errors['difficulty'] = 'exercises.invalid_difficulty';
        }

        return $errors;
    }

    /**
     * Get validation errors for bodyweight entry
     */
    public static function validateBodyweightEntry(array $data): array
    {
        $errors = [];

        $weight = isset($data['weight']) ? (float) $data['weight'] : null;
        if (!self::isPositiveNumber($weight)) {
            $errors['weight'] = 'bodyweight.invalid_weight';
        }

        $validUnits = ['kg', 'lbs'];
        if (!in_array($data['unit'] ?? '', $validUnits)) {
            $errors['unit'] = 'bodyweight.invalid_unit';
        }

        return $errors;
    }
}