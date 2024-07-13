<?php

use Config\AppConfig;
use Core\{Container, Database};
use App\Services\{AuthService, IssueService, UserService};
use Core\Http\DatabaseSessionHandler;

Container::binds(
    Database::class,
    fn () => new Database(
        AppConfig::DB_DRIVER,
        AppConfig::DB_CONFIG,
        AppConfig::DB_CREDENTIALS,
        AppConfig::DB_OPTIONS
    )
);

Container::binds(UserService::class, fn () => new UserService(Container::resolve(Database::class)));

Container::binds(AuthService::class, fn () => new AuthService(Container::resolve(Database::class)));
Container::binds(IssueService::class, fn () => new IssueService(Container::resolve(Database::class), Container::resolve(UserService::class)));

Container::binds(DatabaseSessionHandler::class, fn () => new DatabaseSessionHandler(Container::resolve(Database::class)));
