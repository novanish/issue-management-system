<?php

namespace App\Middlewares;

use Core\Interfaces\MiddlewareInterface;

class TestMiddleware implements MiddlewareInterface
{
    public function process(callable $next): void
    {
        $next();
    }
}
