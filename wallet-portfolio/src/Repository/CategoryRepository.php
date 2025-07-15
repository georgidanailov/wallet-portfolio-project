<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findCategoriesWithProducts(): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.products', 'p')
            ->groupBy('c.id')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByName(string $name): ?Category
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getProductCount(Category $category): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(p.id)')
            ->join('c.products', 'p')
            ->andWhere('c.id = :categoryId')
            ->setParameter('categoryId', $category->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
