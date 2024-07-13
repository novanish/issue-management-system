<?php

namespace App\Middlewares;

use Core\Http\Request;
use Core\Http\Session;
use Core\Interfaces\MiddlewareInterface;


class CSRFGaurdMiddleware implements MiddlewareInterface
{
    public function process(callable $next): void
    {
        $token = Request::body('CSRF');
        if (!$token || !hash_equals(Session::get('CSRF'), $token)) {
            echo "😜";
            exit();
        } else {
            $next();
        }
    }
}
