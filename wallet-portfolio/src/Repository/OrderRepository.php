<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findCompletedOrders(): array
    {
        return $this->findBy(['status' => 'completed'], ['createdAt' => 'DESC']);
    }

    public function findRecentOrders(int $limit = 50): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findOrdersByStatus(string $status): array
    {
        return $this->findBy(['status' => $status], ['createdAt' => 'DESC']);
    }

    public function getTotalOrdersCount(): int
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalSpentAmount(): float
    {
        $result = $this->createQueryBuilder('o')
            ->select('SUM(o.totalAmount)')
            ->andWhere('o.status = :status')
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float)$result : 0.0;
    }

    public function findOrdersInDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.createdAt >= :startDate')
            ->andWhere('o.createdAt <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
