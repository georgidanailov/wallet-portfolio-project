<?php

namespace App\Service;

use App\Repository\OrderRepository;
use App\Repository\CategoryRepository;
use App\Repository\OrderItemRepository;
use App\DTO\CategorySpendingDTO;
use App\DTO\SpendingReportDTO;

class ReportService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private CategoryRepository $categoryRepository,
        private OrderItemRepository $orderItemRepository
    ) {}

    public function generateSpendingReport(): SpendingReportDTO
    {
        $completedOrders = $this->orderRepository->findBy(['status' => 'completed']);
        $categories = $this->categoryRepository->findAll();

        $categorySpendingData = [];
        $totalSpent = 0;
        $totalOrders = count($completedOrders);
        $totalItems = 0;

        foreach ($categories as $category) {
            $categorySpending = $this->calculateCategorySpending($category);

            if ($categorySpending->getTotalSpent() > 0) {
                $categorySpendingData[] = $categorySpending;
                $totalSpent += $categorySpending->getTotalSpent();
            }
        }

        // Calculate total items across all categories
        foreach ($completedOrders as $order) {
            foreach ($order->getOrderItems() as $orderItem) {
                $totalItems += $orderItem->getQuantity();
            }
        }

        // Sort categories by spending (highest first)
        usort($categorySpendingData, function(CategorySpendingDTO $a, CategorySpendingDTO $b) {
            return $b->getTotalSpent() <=> $a->getTotalSpent();
        });

        return new SpendingReportDTO(
            $categorySpendingData,
            $totalSpent,
            $totalOrders,
            $totalItems
        );
    }

    private function calculateCategorySpending($category): CategorySpendingDTO
    {
        $totalSpent = 0;
        $orderCount = 0;
        $totalQuantity = 0;
        $processedOrders = [];

        foreach ($category->getProducts() as $product) {

            $orderItems = $this->orderItemRepository->findBy(['product' => $product]);

            foreach ($orderItems as $orderItem) {

                if ($orderItem->getOrderEntity()->getStatus() === 'completed') {
                    $itemTotal = $orderItem->getQuantity() * $orderItem->getPrice();
                    $totalSpent += $itemTotal;
                    $totalQuantity += $orderItem->getQuantity();

                    $orderId = $orderItem->getOrderEntity()->getId();
                    if (!in_array($orderId, $processedOrders)) {
                        $orderCount++;
                        $processedOrders[] = $orderId;
                    }
                }
            }
        }

        return new CategorySpendingDTO(
            $category->getName(),
            $totalSpent,
            $orderCount,
            $totalQuantity
        );
    }

    public function getCategorySpending(int $categoryId): CategorySpendingDTO
    {
        $category = $this->categoryRepository->find($categoryId);

        if (!$category) {
            throw new \InvalidArgumentException('Category not found');
        }

        return $this->calculateCategorySpending($category);
    }
}