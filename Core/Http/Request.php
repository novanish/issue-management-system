<?php

namespace Core\Http;

/**
 * Represents an HTTP request.
 */
class Request
{
    public const ACTION_NAME = '__ACTION';
    private static array $body = [];

    /**
     * Retrieves the HTTP method of the current request.
     *
     * @return string The HTTP method (e.g., GET, POST, PUT, DELETE).
     */
    public static function getMethod(): string
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        if ($requestMethod !== 'POST') {
            return $requestMethod;
        }

        return strtoupper($_POST[self::ACTION_NAME] ?? $requestMethod);
    }

    /**
     * Gets the request URI.
     *
     * @return string The current URI
     */
    public static function getRequestURI(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Gets the current path from the request URI.
     *
     * @return string The current path
     */
    public static function getCurrentPath(): string
    {
        $parsedUri = parse_url($_SERVER['REQUEST_URI']);
        return $parsedUri['path'];
    }

    /**
     * Retrieves the body parameters of the current request.
     *
     * @return mixed The body parameters of the request.
     */
    public static function body(string $key = null, mixed $default = null): mixed
    {
        static::$body = static::getMethod() === "GET" ? $_GET : $_POST;
        return $key === null ?  static::$body : static::$body[$key] ?? $default;
    }

    /**
     * Retrieves a specific parameter from the request body.
     *
     * @param string $key The key of the parameter to retrieve.
     *
     * @return mixed The value associated with the specified key.
     */
    public static function getFromBody(string $key, mixed $default = null)
    {
        return static::$body[$key] ?? $default;
    }


    /**
     * Gets the query parameters from the current request.
     *
     * If a specific key is provided, returns the corresponding query parameter value; otherwise, returns all query parameters.
     *
     * @param string|null $key     (Optional) The key of the query parameter to retrieve.
     * @param mixed       $default (Optional) The default value to return if the query parameter is not found.
     *
     * @return mixed|array|null The query parameter value if a key is provided and found, an array of all query parameters if no key is provided, or null if the query parameters are not available.
     */
    public static function getQueryParameter(string|array $parameterName = null, ?string $default = null): array|string|null
    {
        if (is_array($parameterName)) {
            return array_reduce($parameterName, function ($acc, $k) {
                $acc[$k] = isset($_GET[$k]) ?  sanitize($_GET[$k]) : null;

                return $acc;
            }, []);
        }

        if ($parameterName !== null) {
            return isset($_GET[$parameterName]) ? sanitize($_GET[$parameterName]) : $default;
        }


        return sanitize($_GET);
    }
}
