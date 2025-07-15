<?php

namespace App\Service;

use App\Repository\OrderItemRepository;
use App\DTO\CategorySpendingDTO;
use App\DTO\SpendingReportDTO;

class ReportService
{
    public function __construct(
        private OrderItemRepository $orderItemRepository
    ) {}

    public function generateSpendingReport(): SpendingReportDTO
    {
        $categoryData = $this->orderItemRepository->getSpendingReportData();

        $totals = $this->orderItemRepository->getOverallTotals();

        $categorySpendingDTOs = array_map(
            fn($data) => new CategorySpendingDTO(
                $data['categoryName'],
                (float) $data['totalSpent'],
                (int) $data['orderCount'],
                (int) $data['totalQuantity']
            ),
            $categoryData
        );

        return new SpendingReportDTO(
            $categorySpendingDTOs,
            (float) ($totals['totalSpent'] ?? 0),
            (int) ($totals['totalOrders'] ?? 0),
            (int) ($totals['totalItems'] ?? 0)
        );
    }

    public function getCategorySpending(int $categoryId): CategorySpendingDTO
    {
        $data = $this->orderItemRepository->getCategorySpendingById($categoryId);

        if (!$data) {
            throw new \InvalidArgumentException('Category not found or has no purchases');
        }

        return new CategorySpendingDTO(
            $data['categoryName'],
            (float) $data['totalSpent'],
            (int) $data['orderCount'],
            (int) $data['totalQuantity']
        );
    }
}