<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.category = :category')
            ->setParameter('category', $category)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.price > 0')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByPriceRange(float $minPrice, float $maxPrice): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.price >= :minPrice')
            ->andWhere('p.price <= :maxPrice')
            ->setParameter('minPrice', $minPrice)
            ->setParameter('maxPrice', $maxPrice)
            ->orderBy('p.price', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function searchByName(string $searchTerm): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.name LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findMostExpensive(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.price', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
