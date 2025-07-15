<?php

namespace App\Repository;

use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderItem>
 */
class OrderItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItem::class);
    }

    public function findByProduct(Product $product): array
    {
        return $this->createQueryBuilder('oi')
            ->andWhere('oi.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->getResult();
    }

    public function findCompletedOrderItemsByProduct(Product $product): array
    {
        return $this->createQueryBuilder('oi')
            ->join('oi.orderEntity', 'o')
            ->andWhere('oi.product = :product')
            ->andWhere('o.status = :status')
            ->setParameter('product', $product)
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getResult();
    }

    public function getTotalQuantitySold(Product $product): int
    {
        $result = $this->createQueryBuilder('oi')
            ->select('SUM(oi.quantity)')
            ->join('oi.orderEntity', 'o')
            ->andWhere('oi.product = :product')
            ->andWhere('o.status = :status')
            ->setParameter('product', $product)
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (int)$result : 0;
    }

    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('oi')
            ->join('oi.product', 'p')
            ->join('oi.orderEntity', 'o')
            ->andWhere('p.category = :category')
            ->andWhere('o.status = :status')
            ->setParameter('category', $category)
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getResult();
    }

    public function getCategorySpendingData(Category $category): array
    {
        return $this->createQueryBuilder('oi')
            ->select('
                SUM(oi.quantity * oi.price) as totalSpent,
                COUNT(DISTINCT oi.orderEntity) as orderCount,
                SUM(oi.quantity) as totalQuantity
            ')
            ->join('oi.product', 'p')
            ->join('oi.orderEntity', 'o')
            ->andWhere('p.category = :category')
            ->andWhere('o.status = :status')
            ->setParameter('category', $category)
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleResult();
    }

    public function getTotalItemsSold(): int
    {
        $result = $this->createQueryBuilder('oi')
            ->select('SUM(oi.quantity)')
            ->join('oi.orderEntity', 'o')
            ->andWhere('o.status = :status')
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (int)$result : 0;
    }
}