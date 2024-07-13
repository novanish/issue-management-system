<?php

namespace App\Middlewares;

use Config\AppConfig;
use Core\Interfaces\MiddlewareInterface;

class ErrorHandlerMiddleware implements MiddlewareInterface
{

    public function process(callable $next): void
    {
        // set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        //     throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        // });

        // set_exception_handler(function ($exception) {
        //     http_response_code(500);
        //     echo "<h1>500 - Internal Server Error</h1>";
        //     echo "<p>Something went wrong. Please try again later.</p>";
        //     exit;
        // });

        try {
            $next();
        } catch (\Exception $e) {
            if (AppConfig::ENV !== AppConfig::ENV_PRODUCTION) {
                throw $e;
            }

            (new \App\Controllers\ErrorsController)->internalServerError();
        }
    }
}
