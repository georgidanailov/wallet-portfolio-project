<?php

namespace App\Exception;

class InsufficientFundsException extends \Exception
{
    public function __construct(string $message = 'Insufficient funds', int $code = 400, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
