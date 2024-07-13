<?php

namespace Core;

use InvalidArgumentException;

/**
 * A simple dependency resolution container.
 */
class Container
{
    /** @var array An array to store bindings. */
    private static array $bindings = [];

    /** @var array An array to store resolved instances. */
    private static array $instances = [];

    /**
     * Bind a resolver to a key.
     *
     * @param string $key The key to bind.
     * @param callable $resolver The resolver function.
     * @return void
     */
    public static function binds(string $key, callable $resolver): void
    {
        static::$bindings[$key] = $resolver;
    }

    /**
     * Resolve an instance from the container.
     *
     * @param string $key The key to resolve.
     * @param bool $shouldCreate Whether to create a new instance if not already resolved.
     * @return mixed The resolved instance.
     * @throws InvalidArgumentException When no matching binding is found.
     */
    public static function resolve(string $key, bool $shouldCreate = false)
    {
        if (!$shouldCreate && array_key_exists($key, static::$instances)) {
            return static::$instances[$key];
        }

        if (!array_key_exists($key, static::$bindings)) {
            throw new InvalidArgumentException("No matching binding found for $key");
        }

        $resolver = static::$bindings[$key];
        $instance = $resolver();
        static::$instances[$key] = $instance;

        return $instance;
    }
}
