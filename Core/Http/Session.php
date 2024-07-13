<?php

namespace Core\Http;

/**
 * Class Session
 * Provides methods to manage session data and flash messages.
 */
class Session
{
    private const FLASH = '__flash';

    /**
     * Set a session value.
     *
     * @param string $key The key to set
     * @param mixed $value The value to set
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Set a flash message.
     *
     * @param string $key The key of the flash message
     * @param mixed $value The value of the flash message
     * @return void
     */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION[static::FLASH][$key] = $value;
    }

    /**
     * Remove all flash messages.
     *
     * @return void
     */
    public static function unflash(): void
    {
        unset($_SESSION[static::FLASH]);
    }

    /**
     * Get a session value.
     *
     * @param string $key The key of the session value
     * @param mixed $defaultValue The default value if the key is not found
     * @return mixed The session value
     */
    public static function get(string $key, mixed $defaultValue = null): mixed
    {
        return $_SESSION[$key] ?? $defaultValue;
    }

    /**
     * Get a flash message.
     *
     * @param string $key The key of the flash message
     * @param mixed $defaultValue The default value if the key is not found
     * @return mixed The flash message
     */
    public static function getFlash(string $key, mixed $defaultValue = null): mixed
    {
        return $_SESSION[static::FLASH][$key] ?? $defaultValue;
    }

    /**
     * Destroy the session.
     *
     * @return void
     */
    public static function destroy(): void
    {
        session_destroy();
        $sessionParams = session_get_cookie_params();
        unset($_SESSION);
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $sessionParams['path'],
            $sessionParams['domain'],
            $sessionParams['secure'],
            $sessionParams['httponly']
        );

        session_regenerate_id();
    }

    /**
     * Check if a session key exists.
     *
     * @param string $key The key to check
     * @return bool True if the key exists, false otherwise
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Get an old input value from flash data.
     *
     * @param string $key The key of the old input value
     * @param mixed $defaultValue The default value if the key is not found
     * @return mixed The old input value
     */
    public static function old(string $key, mixed $defaultValue = null): mixed
    {
        return static::getFlash('old')[$key] ?? $defaultValue;
    }
}
