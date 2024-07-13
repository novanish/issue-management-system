<?php

namespace App\Middlewares;

use App\Services\AuthService;
use Core\Container;
use Core\Http\{Session};
use Core\Interfaces\MiddlewareInterface;

class PersistentLoginMiddleware implements MiddlewareInterface
{
    public function process(callable $next): void
    {

        if (Session::has('user')) {
            $next();
            return;
        }

        $parseRemeberMeToken = parseRememberMeToken();
        if ($parseRemeberMeToken) {
            $authService = Container::resolve(AuthService::class);
            $authService->setSessionFromRememberMeToken($parseRemeberMeToken);
        }

        $next();
    }
}
