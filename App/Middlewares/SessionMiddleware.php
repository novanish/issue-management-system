<?php

namespace App\Middlewares;

use Config\AppConfig;
use Core\Container;
use Core\Http\DatabaseSessionHandler;
use Core\Http\Session;
use Core\Interfaces\MiddlewareInterface;

class SessionMiddleware implements MiddlewareInterface
{

    public function __construct()
    {
    }

    public function process(callable $next): void
    {
        session_set_save_handler(Container::resolve(DatabaseSessionHandler::class), true);
        session_set_cookie_params(AppConfig::SESSION_COOKIE_OPTIONS);
        session_start();
        // logSessionAndCookie('session_log.txt');
        $next();


        Session::unflash();
    }
}
