<?php

namespace App\DTO;

class CreateOrderDTO
{
    public function __construct(
        private int $productId,
        private int $quantity = 1
    ) {}

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function validate(): array
    {
        $errors = [];

        if ($this->productId <= 0) {
            $errors[] = 'Product ID must be greater than 0';
        }

        if ($this->quantity <= 0) {
            $errors[] = 'Quantity must be greater than 0';
        }

        return $errors;
    }
}