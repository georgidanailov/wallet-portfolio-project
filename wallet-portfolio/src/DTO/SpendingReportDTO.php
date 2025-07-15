<?php

namespace App\DTO;

class SpendingReportDTO
{
    public function __construct(
        private array $categorySpending,
        private float $totalSpent,
        private int $totalOrders,
        private int $totalItems
    ) {}

    public function getCategorySpending(): array
    {
        return $this->categorySpending;
    }

    public function getTotalSpent(): float
    {
        return $this->totalSpent;
    }

    public function getTotalOrders(): int
    {
        return $this->totalOrders;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function toArray(): array
    {
        return [
            'categorySpending' => array_map(fn($cs) => $cs->toArray(), $this->categorySpending),
            'totalSpent' => $this->totalSpent,
            'totalOrders' => $this->totalOrders,
            'totalItems' => $this->totalItems
        ];
    }
}