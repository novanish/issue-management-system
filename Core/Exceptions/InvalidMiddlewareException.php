<?php

namespace Core\Exceptions;

/**
 * Class InvalidMiddlewareException
 * Exception thrown when an invalid middleware is encountered.
 */
class InvalidMiddlewareException extends \InvalidArgumentException
{
    /**
     * InvalidMiddlewareException constructor.
     *
     * @param string $message The exception message.
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
