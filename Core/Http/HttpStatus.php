<?php

namespace Core\Http;

/**
 * Class HttpStatus
 * Defines HTTP status codes for convenience.
 */
class HttpStatus
{
    const OK = 200;

    const FOUND = 302;

    const NOT_FOUND = 404;
    const UNAUTHORIZED = 401;
    const BAD_REQUEST = 400;
    const FORBIDDEN = 403;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;

    const INTERNAL_SERVER_ERROR = 500;
}
