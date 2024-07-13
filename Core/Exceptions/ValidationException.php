<?php

namespace Core\Exceptions;

/**
 * Class ValidationException
 * Exception thrown when validation fails.
 */
class ValidationException extends \RuntimeException
{
    /**
     * @var array An array containing validation errors.
     */
    private array $errors;

    /**
     * ValidationException constructor.
     *
     * @param array $errors An array containing validation errors.
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct();
    }

    /**
     * Get the validation errors.
     *
     * @return array An array containing validation errors.
     */
    public function getValidationErrors(): array
    {
        return $this->errors;
    }
}
