<?php

namespace App\Utilities;

use RedBeanPHP\R;
use App\Models\User;

class Auth
{
    private const SESSION_USER_ID = 'user_id';
    private const SESSION_USER_DATA = 'user_data';
    private const SESSION_2FA_PENDING = '2fa_pending_user_id';
    private const SESSION_2FA_VERIFIED = '2fa_verified';

    /**
     * Attempt to log in a user
     *
     * @return array ['success' => bool, 'requires2fa' => bool, 'user' => ?User]
     */
    public static function attemptLogin(string $email, string $password): array
    {
        // Find user by email
        $bean = R::findOne('users', 'email = ?', [$email]);

        if (!$bean) {
            return ['success' => false, 'requires2fa' => false, 'user' => null];
        }

        // Verify password
        if (!password_verify($password, $bean->password_hash)) {
            return ['success' => false, 'requires2fa' => false, 'user' => null];
        }

        $user = User::fromBean($bean);

        // Check if 2FA is enabled
        if ($user->twoFactorEnabled) {
            // Store user ID in session for 2FA verification
            $_SESSION[self::SESSION_2FA_PENDING] = $user->id;
            return ['success' => false, 'requires2fa' => true, 'user' => $user];
        }

        // Log in without 2FA
        self::loginUser($user);
        return ['success' => true, 'requires2fa' => false, 'user' => $user];
    }

    /**
     * Log in a user (set session data)
     */
    public static function loginUser(User $user): void
    {
        $_SESSION[self::SESSION_USER_ID] = $user->id;
        $_SESSION[self::SESSION_USER_DATA] = [
            'id' => $user->id,
            'email' => $user->email,
            'username' => $user->username,
            'role' => $user->role
        ];

        // Clear any pending 2FA
        unset($_SESSION[self::SESSION_2FA_PENDING]);
    }

    /**
     * Verify 2FA code
     */
    public static function verify2FACode(string $code): bool
    {
        if (!isset($_SESSION[self::SESSION_2FA_PENDING])) {
            return false;
        }

        $userId = $_SESSION[self::SESSION_2FA_PENDING];
        $bean = R::load('users', $userId);

        if (!$bean->id || !$bean->two_factor_enabled) {
            return false;
        }

        // Verify TOTP code
        $totp = new \OTPHP\TOTP('', $bean->two_factor_secret, 30, 'sha1', 6);
        
        if ($totp->now() === $code) {
            $user = User::fromBean($bean);
            self::loginUser($user);
            $_SESSION[self::SESSION_2FA_VERIFIED] = true;
            return true;
        }

        return false;
    }

    /**
     * Log out the current user
     */
    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_USER_ID]);
        unset($_SESSION[self::SESSION_USER_DATA]);
        unset($_SESSION[self::SESSION_2FA_PENDING]);
        unset($_SESSION[self::SESSION_2FA_VERIFIED]);
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION[self::SESSION_USER_ID]);
    }

    /**
     * Get the current logged-in user
     */
    public static function getCurrentUser(): ?User
    {
        if (!self::isLoggedIn()) {
            return null;
        }

        $userId = $_SESSION[self::SESSION_USER_ID];
        $bean = R::load('users', $userId);

        if (!$bean->id) {
            return null;
        }

        return User::fromBean($bean);
    }

    /**
     * Get user ID from session
     */
    public static function getUserId(): ?int
    {
        return $_SESSION[self::SESSION_USER_ID] ?? null;
    }

    /**
     * Check if current user is an admin
     */
    public static function isAdmin(): bool
    {
        $userData = $_SESSION[self::SESSION_USER_DATA] ?? null;
        return isset($userData['role']) && $userData['role'] === 'admin';
    }

    /**
     * Require authentication (redirect if not logged in)
     */
    public static function requireAuth(string $redirectUrl = '/login'): bool
    {
        if (!self::isLoggedIn()) {
            FlashMessage::error('errors.unauthorized');
            header('Location: ' . $redirectUrl);
            exit;
        }
        return true;
    }

    /**
     * Require admin role (redirect if not admin)
     */
    public static function requireAdmin(string $redirectUrl = '/'): bool
    {
        if (!self::isLoggedIn() || !self::isAdmin()) {
            FlashMessage::error('errors.admin_only');
            header('Location: ' . $redirectUrl);
            exit;
        }
        return true;
    }

    /**
     * Generate 2FA secret for a user
     *
     * @return array ['secret' => string, 'qrCodeUrl' => string]
     */
    public static function generate2FASecret(User $user): array
    {
        $secret = bin2hex(random_bytes(32));
        
        $totp = new \OTPHP\TOTP('GAINZ', $secret, 30, 'sha1', 6);
        $totp->setLabel($user->email);

        $qrCodeUrl = $totp->getProvisioningUri();

        return [
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl
        ];
    }

    /**
     * Enable 2FA for a user
     */
    public static function enable2FA(int $userId, string $secret): bool
    {
        $bean = R::load('users', $userId);
        
        if (!$bean->id) {
            return false;
        }

        $bean->two_factor_enabled = true;
        $bean->two_factor_secret = $secret;
        
        R::store($bean);
        return true;
    }

    /**
     * Disable 2FA for a user
     */
    public static function disable2FA(int $userId): bool
    {
        $bean = R::load('users', $userId);
        
        if (!$bean->id) {
            return false;
        }

        $bean->two_factor_enabled = false;
        $bean->two_factor_secret = null;
        
        R::store($bean);
        return true;
    }

    /**
     * Register a new user
     */
    public static function registerUser(array $data): array
    {
        // Validate input
        $errors = Validator::validateRegistration($data);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors, 'user' => null];
        }

        // Check if email exists
        $existingEmail = R::findOne('users', 'email = ?', [$data['email']]);
        if ($existingEmail) {
            return [
                'success' => false,
                'errors' => ['email' => 'auth.email_exists'],
                'user' => null
            ];
        }

        // Check if username exists
        $existingUsername = R::findOne('users', 'username = ?', [$data['username']]);
        if ($existingUsername) {
            return [
                'success' => false,
                'errors' => ['username' => 'auth.username_exists'],
                'user' => null
            ];
        }

        // Create new user
        $bean = R::dispense('users');
        $bean->email = $data['email'];
        $bean->username = $data['username'];
        $bean->password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $bean->date_of_birth = $data['date_of_birth'];
        $bean->role = 'user';
        $bean->two_factor_enabled = false;

        R::store($bean);

        $user = User::fromBean($bean);
        return ['success' => true, 'errors' => [], 'user' => $user];
    }

    /**
     * Get user data from session (cached)
     */
    public static function getSessionUser(): ?array
    {
        return $_SESSION[self::SESSION_USER_DATA] ?? null;
    }
}