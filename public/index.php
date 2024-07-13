<?php

use Config\AppConfig;
use Core\App;
use Core\Http\Cookie;

define("BASE_PATH", dirname(__DIR__));

require BASE_PATH . DIRECTORY_SEPARATOR . "Utils" . DIRECTORY_SEPARATOR . "functions.php";

spl_autoload_register(fn ($class) => require basePath("/{$class}.php"));
require basePath('/utils/binds.php');

date_default_timezone_set(AppConfig::TIMEZONE);
Cookie::setDefaultOptions(AppConfig::COOKIE_DEFAULT_OPTIONS);
$app = new App();
$app->initialize();
