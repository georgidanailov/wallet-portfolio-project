<?php

namespace App\Service;

use App\DTO\PaginationDTO;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Repository\OrderRepository;
use App\Exception\InsufficientFundsException;
use App\Exception\ProductNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class OrderService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository,
        private WalletService $walletService,
        private LoggerInterface $logger
    ) {}

    public function createOrder(Product $product, int $quantity = 1): Order
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than 0');
        }

        $totalAmount = $product->getPrice() * $quantity;

        if (!$this->walletService->hasEnoughFunds($totalAmount)) {
            throw new InsufficientFundsException('Insufficient funds to complete purchase');
        }

        $this->entityManager->beginTransaction();

        try {
            $order = new Order();
            $order->setTotalAmount($totalAmount);
            $order->setStatus('completed');
            $order->setCreatedAt(new \DateTimeImmutable());

            $orderItem = new OrderItem();
            $orderItem->setQuantity($quantity);
            $orderItem->setPrice($product->getPrice());
            $orderItem->setOrder($order);
            $orderItem->setProduct($product);

            $this->walletService->deductFunds(
                $totalAmount,
                sprintf('Purchased: %s (x%d)', $product->getName(), $quantity)
            );

            $this->entityManager->persist($order);
            $this->entityManager->persist($orderItem);
            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->logger->info('Order created successfully', [
                'order_id' => $order->getId(),
                'product_id' => $product->getId(),
                'quantity' => $quantity,
                'total_amount' => $totalAmount
            ]);

            return $order;

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $this->logger->error('Failed to create order', [
                'product_id' => $product->getId(),
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getOrderHistory(int $limit = 50): array
    {
        return $this->orderRepository->findRecentOrders($limit);
    }

    public function getPaginatedOrders(int $page = 1, int $itemsPerPage = 5): PaginationDTO
    {
        // Validate page number
        if ($page < 1) {
            $page = 1;
        }

        $orders = $this->orderRepository->findPaginatedOrders($page, $itemsPerPage);
        $totalOrders = $this->orderRepository->countAllOrders();

        return new PaginationDTO(
            $orders,
            $page,
            $itemsPerPage,
            $totalOrders
        );
    }

    public function getOrderById(int $orderId): ?Order
    {
        return $this->orderRepository->find($orderId);
    }

    public function getOrdersByStatus(string $status): array
    {
        return $this->orderRepository->findOrdersByStatus($status);
    }

    public function getCompletedOrders(): array
    {
        return $this->orderRepository->findCompletedOrders();
    }
}