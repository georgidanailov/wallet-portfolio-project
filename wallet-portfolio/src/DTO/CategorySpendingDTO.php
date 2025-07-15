<?php

namespace App\DTO;

class CategorySpendingDTO
{
    public function __construct(
        private string $categoryName,
        private float $totalSpent,
        private int $orderCount,
        private int $totalQuantity
    ) {}

    public function getCategoryName(): string
    {
        return $this->categoryName;
    }

    public function getTotalSpent(): float
    {
        return $this->totalSpent;
    }

    public function getOrderCount(): int
    {
        return $this->orderCount;
    }

    public function getTotalQuantity(): int
    {
        return $this->totalQuantity;
    }

    public function toArray(): array
    {
        return [
            'categoryName' => $this->categoryName,
            'totalSpent' => $this->totalSpent,
            'orderCount' => $this->orderCount,
            'totalQuantity' => $this->totalQuantity
        ];
    }
}
