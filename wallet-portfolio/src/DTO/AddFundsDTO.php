<?php

namespace App\DTO;

class AddFundsDTO
{
    public function __construct(
        private float $amount,
        private string $description = 'Added funds to wallet'
    ) {}

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function validate(): array
    {
        $errors = [];

        if ($this->amount <= 0) {
            $errors[] = 'Amount must be greater than 0';
        }

        if (empty(trim($this->description))) {
            $errors[] = 'Description cannot be empty';
        }

        return $errors;
    }
}