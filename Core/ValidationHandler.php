<?php

namespace Core;

use Core\Exceptions\ValidationException;

/**
 * Class ValidationHandler
 * Handles validation of input values based on provided validators.
 */
class ValidationHandler
{
    /**
     * Validates input values against provided validators.
     *
     * @param array $validators An array containing validators for each field.
     * @param array $values     An array containing values to be validated.
     *
     * @return array            Returns an array of validated values.
     *
     * @throws ValidationException If validation fails, an exception containing validation errors is thrown.
     */
    public static function validate(array $validators, array $values): array
    {
        $errors = [];
        $validatedValues = [];

        foreach ($validators as $fieldName => $validator) {
            $error = $validator->getErrors();
            if ($error) {
                $errors[$fieldName] = $error;
            }

            if (isset($values[$fieldName])) {
                $validatedValues[$fieldName] = $validator->getValue();
            }
        }

        if (count($errors) !== 0) {
            throw new ValidationException($errors);
        }

        return $validatedValues;
    }
}
