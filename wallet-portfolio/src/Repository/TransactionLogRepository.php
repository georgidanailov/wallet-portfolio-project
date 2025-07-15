<?php

namespace App\Repository;

use App\Entity\TransactionLog;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransactionLog>
 */
class TransactionLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionLog::class);
    }

    public function findRecentTransactions(Wallet $wallet, int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.wallet = :wallet')
            ->setParameter('wallet', $wallet)
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findTransactionsByType(Wallet $wallet, string $type): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.wallet = :wallet')
            ->andWhere('t.type = :type')
            ->setParameter('wallet', $wallet)
            ->setParameter('type', $type)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalSpentAmount(Wallet $wallet): float
    {
        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->andWhere('t.wallet = :wallet')
            ->andWhere('t.type = :type')
            ->setParameter('wallet', $wallet)
            ->setParameter('type', 'spend')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float)$result : 0.0;
    }

    public function getTotalAddedAmount(Wallet $wallet): float
    {
        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->andWhere('t.wallet = :wallet')
            ->andWhere('t.type = :type')
            ->setParameter('wallet', $wallet)
            ->setParameter('type', 'add')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float)$result : 0.0;
    }
}
