<?php

namespace Core\Exceptions;

/**
 * Exception thrown when an invalid route handler is provided.
 * An invalid handler can be either a non-callable value or a string in the incorrect format.
 * The correct format for a handler string is "ClassName@methodName".
 */
class InvalidRouteHandlerException extends \InvalidArgumentException
{
    /**
     * Constructor.
     *
     * @param string $message The error message to display
     */
    public function __construct(string $message)
    {

        parent::__construct($message);
    }
}
