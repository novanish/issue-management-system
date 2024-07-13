<?php

namespace Core\Validators;

use Core\Interfaces\ValidatorInterface;

/**
 * Class StringValidator
 * A validator class for string inputs.
 */
class StringValidator implements ValidatorInterface
{
    private array $errors = [];

    /**
     * StringValidator constructor.
     *
     * @param string|null $input The input string to be validated
     * @param bool $doesRequire Whether the input is required or not
     * @param string $errorMessage The error message to be used when input is required but not provided
     */
    public function __construct(
        private ?string &$input = null,
        private bool $doesRequire = true,
        string $errorMessage = 'Input is required.'
    ) {
        if ($doesRequire && $this->input === null) $this->errors[] = $errorMessage;
    }

    /**
     * Set an error message if a condition is met.
     *
     * @param callable $cb The callback function to check a condition
     * @param string $errorMessage The error message to set if the condition is met
     * @return $this
     */
    private function setError(callable $cb, string $errorMessage): self
    {
        if (!$this->doesRequire && $this->input === null) {
            return $this;
        }

        if ($cb()) {
            $this->errors[] = $errorMessage;
        }

        return $this;
    }

    /**
     * Validate minimum length of input.
     *
     * @param int $minLength The minimum length required
     * @param string|null $errorMessage Optional custom error message
     * @return $this
     */
    public function minLength(int $minLength, ?string $errorMessage = null): self
    {
        $errorMessage ??= "Input length must be at least {$minLength} characters.";

        return $this->setError(fn () => strlen($this->input) < $minLength, $errorMessage);
    }
    /** * Validate maximum length of input. * * @param int $maxLength The maximum length allowed * @param string|null $errorMessage Optional custom error message * @return $this */ public function maxLength(int $maxLength, ?string $errorMessage = null): self
    {
        $errorMessage ??= "Input length must not exceed {$maxLength} characters.";
        return $this->setError(fn () => strlen($this->input) > $maxLength, $errorMessage);
    }

    /**
     * Validate if input is equal to a given value.
     *
     * @param string $comparisonString The string to compare against
     * @param string|null $errorMessage Optional custom error message
     * @return $this
     */
    public function equals(string $comparisonString, ?string $errorMessage = null): self
    {
        $errorMessage ??= 'Input must match the provided value.';
        return $this->setError(fn () => $this->input !== $comparisonString, $errorMessage);
    }

    /**
     * Validate input against a regex pattern.
     *
     * @param string $pattern The regex pattern to match against
     * @param string|null $errorMessage Optional custom error message
     * @return $this
     */
    public function pattern(string $pattern, ?string $errorMessage = null): self
    {
        $errorMessage ??= 'Input format is invalid.';
        $doesMatch = (bool) preg_match($pattern, $this->input);

        return $this->setError(fn () => !$doesMatch, $errorMessage);
    }

    /**
     * Validate if the input value is in a given array of options.
     *
     * @param array $options The array of options to compare against
     * @param string|null $errorMessage Optional custom error message
     * @param bool|null $isCaseSensitive Whether the comparison should be case-sensitive or not
     * @return $this
     */
    public function oneOf(array $options, ?string $errorMessage = null, ?bool $isCaseSensitive = true): self
    {
        $optionsString = implode(', ', $options);
        $errorMessage ??= "The value must be one of: {$optionsString}";

        $callback = function () use ($options, $isCaseSensitive) {
            $input = $isCaseSensitive ? $this->input : strtoupper($this->input);

            foreach ($options as $option) {
                $optionToCompare = $isCaseSensitive ? $option : strtoupper($option);

                if ($input === $optionToCompare) {
                    return false; // Input value found in options
                }
            }

            return true; // Input value not found in options
        };

        return $this->setError($callback, $errorMessage);
    }

    /**
     * Validate if the input is an email address.
     *
     * @param string|null $errorMessage Optional custom error message
     * @return $this
     */
    public function email(?string $errorMessage = null): self
    {
        $errorMessage ??= 'Invalid email format.';
        return $this->setError(fn () => !filter_var($this->input, FILTER_VALIDATE_EMAIL), $errorMessage);
    }

    /**
     * Get all errors.
     *
     * @return array|null
     */
    public function getErrors(): array | null
    {
        return count($this->errors) === 0 ? null : $this->errors;
    }

    /**
     * Get the first error.
     *
     * @return string|null
     */
    public function getError(): string | null
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Trim the input.
     *
     * @return $this
     */
    public function trim(): self
    {
        $this->input = trim($this->input);
        return $this;
    }

    /**
     * Get the validated value.
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->input;
    }

    /**
     * Transform the input using a given callable.
     *
     * @param callable $transformer
     * @return $this
     */
    public function transform(callable $transformer): self
    {
        $this->input = $transformer($this->input);
        return $this;
    }


    /**
     * Add custom validation rule.
     *
     * @param callable $validator The custom validation function
     * @param string $errorMessage The error message for the custom rule
     * @return $this
     */
    public function custom(callable $validator, string $errorMessage): self
    {
        $this->setError(fn () => $validator($this->input, $this), $errorMessage);
        return $this;
    }
}
