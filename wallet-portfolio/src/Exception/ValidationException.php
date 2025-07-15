<?php

namespace App\Exception;

class ValidationException extends \Exception
{
    private array $errors;

    public function __construct(array $errors, string $message = 'Validation failed', int $code = 422, \Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
