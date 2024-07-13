<?php

namespace App\Middlewares;

use Core\Http\{Request, Session};
use Core\Interfaces\MiddlewareInterface;
use Core\Router;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(callable $next): void
    {
        if (!Session::has('user')) {
            $query = http_build_query(["redirectTo" => Request::getRequestURI()]);
            Router::redirectTo("/auth/signin?{$query}");
        } else {
            $next();
        }
    }
}
