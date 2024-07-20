<?php

namespace Core;

use App\Controllers as AppControllers;
use App\Middlewares\{
    AuthMiddleware,
    ValidationExceptionHandlerMiddleware,
    SessionMiddleware,
    ErrorHandlerMiddleware,
    CSRFGaurdMiddleware,
    PersistentLoginMiddleware,
    GuestOnlyMiddleware
};

class App
{

    public function __construct()
    {
    }

    public function initialize()
    {
        $this->registerRoutes();
        $this->registerMiddlewares();
        Router::dispatch();
    }

    public function registerRoutes()
    {
        Router::get('/', AppControllers\HomeController::class . "@index");

        Router::get('/auth/signin', AppControllers\AuthController::class . "@signinView");
        Router::post('/auth/signin', AppControllers\AuthController::class . "@signin");
        Router::get('/auth/signup', AppControllers\AuthController::class . "@signupView");
        Router::post('/auth/signup', AppControllers\AuthController::class . "@signup");
        Router::post('/auth/signout', AppControllers\AuthController::class . "@signout");
        Router::get('/auth/change-password', AppControllers\AuthController::class . "@changePasswordView");
        Router::put('/auth/change-password', AppControllers\AuthController::class . "@changePassword");


        Router::get('/issues', AppControllers\IssueController::class . "@issuesView");
        Router::get('/partial/issues', AppControllers\IssueController::class . "@partialIssuesView");
        Router::get('/issues/create', AppControllers\IssueController::class . "@createIssueView");
        Router::post('/issues/create', AppControllers\IssueController::class . "@createIssue");
        Router::get('/issues/view/:issueId', AppControllers\IssueController::class . "@issueView");
        Router::get('/issues/edit/:issueId', AppControllers\IssueController::class . "@editIssueView");
        Router::put('/issues/edit/:issueId', AppControllers\IssueController::class . "@editIssue");
        Router::delete('/issues/delete/:issueId', AppControllers\IssueController::class . "@deleteIssue");

        Router::get('/issues/delete-logs/download', AppControllers\IssueController::class . "@downloadDeleteLog");
        Router::get('/issues/export-to-csv', AppControllers\IssueController::class . "@exportIssuesAsCSV");

        Router::get('*', AppControllers\ErrorsController::class . "@notFound");
    }

    public function registerMiddlewares()
    {
        Router::addMiddleware("*", SessionMiddleware::class, ErrorHandlerMiddleware::class);
        Router::addMiddleware("*", PersistentLoginMiddleware::class, ValidationExceptionHandlerMiddleware::class);
        Router::addMiddleware("/issues/delete/*", CSRFGaurdMiddleware::class);
        Router::addMiddleware("/auth/signin", GuestOnlyMiddleware::class);
        Router::addMiddleware("/auth/signup", GuestOnlyMiddleware::class);
        Router::addMiddleware("/auth/change-password", AuthMiddleware::class);
        Router::addMiddleware("/issues*", AuthMiddleware::class);
        Router::addMiddleware("/partial/issues", AuthMiddleware::class);
    }
}
