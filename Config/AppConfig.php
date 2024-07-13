<?php

namespace Config;

/**
 * Class AppConfig
 * Configuration class containing constants for application settings.
 */
class AppConfig
{
    /**
     * Environment constant for development environment.
     */
    const ENV_DEVELOPMENT = "DEVELOPMENT";

    /**
     * Environment constant for production environment.
     */
    const ENV_PRODUCTION = "PRODUCTION";

    /**
     * Current environment setting.
     */
    const ENV = self::ENV_DEVELOPMENT;

    /**
     * Database driver setting.
     */
    const DB_DRIVER = "mysql";

    /**
     * Database configuration settings.
     */
    const DB_CONFIG = [
        "host" => "localhost",
        "port" => 3306,
        "dbname" => "issue_management_db",
    ];

    /**
     * Database credentials.
     */
    const DB_CREDENTIALS = [
        "username" => "root",
        "password" => "2468"
    ];

    const DB_OPTIONS = [
        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone='+00:00'"
    ];

    /**
     * Session cookie options.
     */
    const SESSION_COOKIE_OPTIONS = [
        "httponly" => true,
        "secure" => self::ENV === self::ENV_PRODUCTION,
        "path" => "/",
        "samesite" => "Strict",
    ];

    const COOKIE_DEFAULT_OPTIONS = [
        "Secure" => self::ENV === self::ENV_PRODUCTION,
    ];

    const TIMEZONE = 'UTC';

    const REMEMBER_ME_EXPIRY_DAYS = 28;

    const REMEMBER_ME_COOKIE_NAME = "__remember_me";
}
