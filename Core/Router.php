<?php

namespace Core;

use Config\AppConfig;
use Core\Http\{HttpStatus, Request};
use Core\Exceptions\{InvalidRouteHandlerException, InvalidMiddlewareException};
use Core\Interfaces\MiddlewareInterface;

/**
 * Class Router
 *
 * A class for routing HTTP requests to appropriate handlers based on specified routes.
 *
 * @package Core
 */
class Router
{
    /**
     * @var array Associative array to store routes based on HTTP methods (GET, POST, DELETE)
     */
    private static array $routes = [
        "GET" => [],
        "POST" => [],
        "PUT" => [],
        "DELETE" => [],
    ];

    /**
     * @var array List of middlewares to be applied to routes
     */
    private static array $middlewares = [];

    /**
     * @var array Associative array to store route parameters
     */
    private static array $params = [];

    /**
     * Validates the route handler.
     *
     * @param string $method The HTTP method (GET, POST, DELETE)
     * @param string $path The URL path for the route
     * @param callable|string $handler The handler for the route, which can be a callable or a string of "ClassName@methodName"
     * @throws InvalidRouteHandlerException When an invalid route handler is provided
     */
    private static function validateRouteHandler(string $method, string $path, callable | string $handler): void
    {
        $isHandlerCallable = is_callable($handler);

        if (!$isHandlerCallable && !strpos($handler, '@')) {
            throw new InvalidRouteHandlerException("Invalid route handler provided for route '{$path}' with method '{$method}'. A route handler must be either a valid callable or a string in the format 'ClassName@methodName'.");
        }

        if ($isHandlerCallable) return;

        [$className, $methodName] = explode('@', $handler);

        if (!class_exists($className)) {
            throw new InvalidRouteHandlerException("Cannot find class '{$className}' specified in the route handler for route '{$path}' with method '{$method}'.");
        }

        $obj = new $className;

        if (!method_exists($obj, $methodName)) {
            throw new InvalidRouteHandlerException("Cannot find the method '{$methodName}' inside the class '{$className}' specified in the route handler for route '{$path}' with method '{$method}'.");
        } elseif (!is_callable([$obj, $methodName])) {
            throw new InvalidRouteHandlerException("The method '{$methodName}' inside the class '{$className}' specified in the route handler for route '{$path}' with method '{$method}' is not callable. The method is either private or protected");
        }
    }


    /**
     * Adds a route to the specified HTTP method.
     *
     * @param string $method The HTTP method (GET, POST, DELETE)
     * @param string $path   The URL path for the route
     * @param callable|string $handler The handler for the route, which can be a callable or a string of "ClassName@methodName"
     * @throws InvalidRouteHandlerException When an invalid route handler is provided
     */
    private static function addRoute(string $method, string $path, callable | string $handler): void
    {

        static::$routes[$method][] = compact('path', 'handler');

        if (AppConfig::ENV !== AppConfig::ENV_PRODUCTION) {
            static::validateRouteHandler($method, $path, $handler);
        };
    }


    /**
     * Adds middleware(s) to be applied to the specified route(s).
     *
     * @param string $path The URL path for the route to which middleware(s) will be applied
     * @param string ...$middlewares The middleware class(es) to be applied to the route(s)
     * @throws InvalidMiddlewareException When an invalid middleware class is provided
     */
    public static function addMiddleware(string $path, string ...$middlewares): void
    {
        array_push(static::$middlewares, compact('path', 'middlewares'));


        if (AppConfig::ENV === AppConfig::ENV_PRODUCTION) return;

        foreach ($middlewares as $middleware) {
            if (!class_exists($middleware)) {
                throw new InvalidMiddlewareException("The provided middleware class '{$middleware}' for route '{$path}' does not exist.");
            }

            $object = new $middleware;
            if (!($object instanceof MiddlewareInterface)) {
                throw new InvalidMiddlewareException("The provided middleware class '{$middleware}' for route '{$path}' does not implement MiddlewareInterface.");
            }
        }
    }

    /**
     * Adds a GET route.
     *
     * @param string $path The URL path for the route
     * @param callable|string $handler The handler for the route, which can be a callable or a string of "ClassName@methodName"
     * @throws InvalidRouteHandlerException When an invalid route handler is provided
     */
    public static function get(string $path, callable | string $handler): void
    {
        static::addRoute('GET', $path, $handler);
    }

    /**
     * Adds a POST route.
     *
     * @param string $path The URL path for the route
     * @param callable|string $handler The handler for the route, which can be a callable or a string of "ClassName@methodName"
     * @throws InvalidRouteHandlerException When an invalid route handler is provided
     */
    public static function post(string $path, callable | string $handler): void
    {
        static::addRoute('POST', $path, $handler);
    }

    /**
     * Adds a PUT route.
     *
     * @param string $path The URL path for the route
     * @param callable|string $handler The handler for the route, which can be a callable or a string of "ClassName@methodName"
     * @throws InvalidRouteHandlerException When an invalid route handler is provided
     */
    public static function put(string $path, callable | string $handler): void
    {
        static::addRoute('PUT', $path, $handler);
    }

    /**
     * Adds a DELETE route.
     *
     * @param string $path The URL path for the route
     * @param callable|string $handler The handler for the route, which can be a callable or a string of "ClassName@methodName"
     * @throws InvalidRouteHandlerException When an invalid route handler is provided
     */
    public static function delete(string $path, callable | string $handler): void
    {
        static::addRoute('DELETE', $path, $handler);
    }


    /**
     * Redirects to a specified location with an optional HTTP response code.
     *
     * @param string $path The destination URL
     * @param int    $responseCode The HTTP response code (default: HttpStatus::FOUND)
     */
    public static function redirectTo(string $path, int $responseCode = HttpStatus::FOUND): void
    {
        header("location: {$path}", response_code: $responseCode);
        exit();
    }

    /**
     * Normalizes the provided path by removing leading and trailing slashes.
     *
     * @param string $path The path to normalize
     * @return string The normalized path
     */
    public static function normalizePath(string $path): string
    {
        $path = trim($path, '/');

        return "/$path";
    }

    /**
     * Builds a regular expression for matching a route with parameters.
     *
     * @param string $route The route path with optional parameters
     * @return string The regular expression for the route
     */
    private static function buildRouteRegex(string $route): string
    {
        $route = static::normalizePath($route);
        $route = preg_replace('#:([a-zA-Z0-9_-]+)#', '(?P<\1>[a-zA-Z0-9_-]+)', $route);
        $route = preg_replace("#\*#", ".*", $route);
        return "#^{$route}/?$#";
    }

    /**
     * Matches the current request path against registered routes.
     *
     * @return callable|null|string The route handler if a match is found, otherwise null
     */
    private static function getMatchingRouteHandler(): callable | null | string
    {
        $method = Request::getMethod();
        $registeredRoutes = static::$routes[$method];

        foreach ($registeredRoutes as $route) {
            if (static::doesPathMatch($route['path'], static::$params)) {
                return $route['handler'];
            }
        }

        return null;
    }

    /**
     * Checks if the current path matches the provided route.
     *
     * @param string $pathToMatch The route to match against
     * @param array|null $matches Reference to store the matched groups (optional)
     * @return bool Whether the route matches the current path
     */
    private static function doesPathMatch(string $pathToMatch, ?array &$matches = null): bool
    {
        $currentPath = Request::getCurrentPath();
        $regexRoute = static::buildRouteRegex($pathToMatch);

        return (bool) preg_match($regexRoute, $currentPath, $matches);
    }


    /**
     * Dispatches the request to the appropriate route and middleware.
     */
    public static function dispatch(): void
    {
        $routeHandler = static::getMatchingRouteHandler();

        $next = is_callable($routeHandler) ? $routeHandler : function () use ($routeHandler) {
            if (!$routeHandler) {
                http_response_code(HttpStatus::NOT_FOUND);
                echo "404 Not Found";
                return;
            }

            [$controller, $action] = explode('@', $routeHandler);

            $controllerInstance = new $controller();
            $controllerInstance->$action();
        };

        for ($i = count(static::$middlewares) - 1; $i !== -1; --$i) {
            ["path" => $path, "middlewares" => $middlewares] = static::$middlewares[$i];
            if (!static::doesPathMatch($path)) continue;


            for ($j = count($middlewares) - 1; $j !== -1; --$j) {
                $middleware = $middlewares[$j];
                $next = fn () => (new $middleware)->process($next);
            }
        }

        $next();
    }


    /**
     * Retrieves the parameters from the current route.
     *
     * @param string|null $param The parameter to retrieve, if specified
     * @return array|string The route parameters if available; otherwise, an empty array
     */
    public static function getRouteParams(?string $param): array | string
    {
        return $param ? static::$params[$param] : static::$params;
    }
}
