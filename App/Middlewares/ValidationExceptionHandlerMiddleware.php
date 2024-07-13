<?php

namespace App\Middlewares;

use Core\Exceptions\ValidationException;
use Core\Http\Request;
use Core\Http\Session;
use Core\Interfaces\MiddlewareInterface;
use Core\Router;

class ValidationExceptionHandlerMiddleware implements MiddlewareInterface
{
    public function process(callable $next): void
    {
        try {
            $next();
        } catch (ValidationException $ex) {
            Session::flash('errors', $ex->getValidationErrors());
            Session::flash('old', Request::body());
            Router::redirectTo($_SERVER['HTTP_REFERER']);
        }
    }
}
