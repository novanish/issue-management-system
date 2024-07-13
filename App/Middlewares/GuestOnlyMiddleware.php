<?php

namespace App\Middlewares;

use Core\Http\{Request, Session};
use Core\Interfaces\MiddlewareInterface;
use Core\Router;

class GuestOnlyMiddleware implements MiddlewareInterface
{
    public function process(callable $next): void
    {
        if (!Session::has('user')) {
            $next();
        } else {
            Router::redirectTo(Request::getQueryParameter('redirectTo', '/'));
        }
    }
}
