<?php

namespace App\Repository;

use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wallet>
 */
class WalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    public function findOrCreateDefaultWallet(): Wallet
    {
        $wallet = $this->findOneBy([]);

        if (!$wallet) {
            $wallet = new Wallet();
            $wallet->setBalance(0.00);
            $this->getEntityManager()->persist($wallet);
            $this->getEntityManager()->flush();
        }

        return $wallet;
    }

    public function updateBalance(Wallet $wallet, float $newBalance): void
    {
        $wallet->setBalance($newBalance);
        $this->getEntityManager()->persist($wallet);
        $this->getEntityManager()->flush();
    }

    public function hasEnoughFunds(float $amount): bool
    {
        $wallet = $this->findOrCreateDefaultWallet();
        return $wallet->getBalance() >= $amount;
    }
}
