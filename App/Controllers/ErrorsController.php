<?php

namespace App\Controllers;

use Core\Http\HttpStatus;

/**
 * Class ErrorsController
 * Controller for handling error pages.
 */
class ErrorsController extends \Core\Controller
{
    /**
     * ErrorsController constructor.
     */
    public function __construct()
    {
    }

    /**
     * Display the 404 Not Found page.
     */
    public function notFound()
    {
        http_response_code(HttpStatus::NOT_FOUND);

        $this
            ->addStyle(css('/errors'))
            ->setPageTitle(HttpStatus::NOT_FOUND . ' - Page Not Found')
            ->renderView("/errors/404");
    }

    /**
     * Display the 500 Internal Server Error page.
     */
    public function internalServerError()
    {
        http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);

        $this
            ->addStyle(css('/errors'))
            ->setPageTitle(HttpStatus::INTERNAL_SERVER_ERROR . ' - Internal Server Error')
            ->renderView("/errors/500");
    }

    /**
     * Display the 403 Forbidden page.
     */
    public function forbidden()
    {
        http_response_code(HttpStatus::FORBIDDEN);

        $this
            ->addStyle(css('/errors'))
            ->setPageTitle(HttpStatus::FORBIDDEN . ' - Forbidden')
            ->renderView("/errors/403");
    }
}
