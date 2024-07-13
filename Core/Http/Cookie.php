<?php

namespace Core\Http;

class Cookie
{
    private static array $defaultOptions = [
        "HttpOnly" => true,
        "Path" => "/",
        "SameSite" => "Strict"
    ];

    public static function get(string $name, mixed $defaultValue = null)
    {
        return $_COOKIE[$name] ?? $defaultValue;
    }

    public static function set(string $name, string $value, array|null $options = null)
    {
        $options  = $options ? array_merge(self::$defaultOptions, $options) : static::$defaultOptions;
        $options = array_filter($options, fn ($v) => !is_bool($v) || $v !== false);
        $options = array_map(fn ($k, $v) => is_bool($v)  ? "$k" : "$k=$v", array_keys($options), $options);
        $cookieHeader = sprintf(
            'Set-Cookie: %s=%s; ',
            urldecode($name),
            urldecode($value)
        ) . implode('; ', $options);

        echo $cookieHeader;
        header($cookieHeader);
    }

    public static function remove(string $name)
    {
        static::set($name, 'DELETED', ["Max-Age" => -10_000]);
    }

    public static function setDefaultOptions(array $options)
    {
        static::$defaultOptions = array_merge(static::$defaultOptions, $options);
    }

    public static function has(string $name)
    {
        return isset($_COOKIE[$name]);
    }
}
