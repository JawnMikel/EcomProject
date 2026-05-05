<?php

namespace App\Utilities;

class FlashMessage
{
    public const TYPE_SUCCESS = 'success';
    public const TYPE_ERROR = 'error';
    public const TYPE_WARNING = 'warning';
    public const TYPE_INFO = 'info';

    private const VALID_TYPES = [
        self::TYPE_SUCCESS,
        self::TYPE_ERROR,
        self::TYPE_WARNING,
        self::TYPE_INFO
    ];

    private const SESSION_KEY = 'flash_messages';

    /**
     * Set a flash message
     *
     * @param string $type Message type (success, error, warning, info)
     * @param string $message The message text
     */
    public static function set(string $type, string $message): void
    {
        if (!in_array($type, self::VALID_TYPES)) {
            $type = self::TYPE_INFO;
        }

        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        $_SESSION[self::SESSION_KEY][] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Set a success message
     */
    public static function success(string $message): void
    {
        self::set(self::TYPE_SUCCESS, $message);
    }

    /**
     * Set an error message
     */
    public static function error(string $message): void
    {
        self::set(self::TYPE_ERROR, $message);
    }

    /**
     * Set a warning message
     */
    public static function warning(string $message): void
    {
        self::set(self::TYPE_WARNING, $message);
    }

    /**
     * Set an info message
     */
    public static function info(string $message): void
    {
        self::set(self::TYPE_INFO, $message);
    }

    /**
     * Get all flash messages and clear them
     *
     * @return array Array of flash messages
     */
    public static function getAndClear(): array
    {
        $messages = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]);
        return $messages;
    }

    /**
     * Get all flash messages without clearing
     *
     * @return array Array of flash messages
     */
    public static function get(): array
    {
        return $_SESSION[self::SESSION_KEY] ?? [];
    }

    /**
     * Check if there are any flash messages
     */
    public static function has(): bool
    {
        return !empty($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Clear all flash messages
     */
    public static function clear(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }
}