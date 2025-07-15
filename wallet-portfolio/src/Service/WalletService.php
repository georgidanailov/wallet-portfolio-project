<?php

namespace App\Service;

use App\Entity\Wallet;
use App\Entity\TransactionLog;
use App\Repository\WalletRepository;
use App\Exception\InsufficientFundsException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class WalletService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WalletRepository $walletRepository,
        private LoggerInterface $logger
    ) {}

    public function getWallet(): Wallet
    {
        return $this->walletRepository->findOrCreateDefaultWallet();
    }

    public function addFunds(float $amount, string $description = 'Added funds to wallet'): Wallet
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than 0');
        }

        $wallet = $this->getWallet();

        $this->entityManager->beginTransaction();

        try {
            $oldBalance = $wallet->getBalance();
            $newBalance = $oldBalance + $amount;
            $wallet->setBalance($newBalance);

            $this->createTransactionLog($wallet, 'add', $amount, $description);

            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->logger->info('Funds added to wallet', [
                'amount' => $amount,
                'old_balance' => $oldBalance,
                'new_balance' => $newBalance
            ]);

            return $wallet;

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $this->logger->error('Failed to add funds to wallet', [
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function deductFunds(float $amount, string $description = 'Funds deducted'): Wallet
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than 0');
        }

        $wallet = $this->getWallet();

        if ($wallet->getBalance() < $amount) {
            throw new InsufficientFundsException('Insufficient funds in wallet');
        }

        $this->entityManager->beginTransaction();

        try {
            $oldBalance = $wallet->getBalance();
            $newBalance = $oldBalance - $amount;
            $wallet->setBalance($newBalance);

            $this->createTransactionLog($wallet, 'spend', $amount, $description);

            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->logger->info('Funds deducted from wallet', [
                'amount' => $amount,
                'old_balance' => $oldBalance,
                'new_balance' => $newBalance
            ]);

            return $wallet;

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $this->logger->error('Failed to deduct funds from wallet', [
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function hasEnoughFunds(float $amount): bool
    {
        return $this->walletRepository->hasEnoughFunds($amount);
    }

    private function createTransactionLog(Wallet $wallet, string $type, float $amount, string $description): void
    {
        $log = new TransactionLog();
        $log->setType($type);
        $log->setAmount($amount);
        $log->setDescription($description);
        $log->setCreatedAt(new \DateTimeImmutable());
        $log->setWallet($wallet);

        $this->entityManager->persist($log);
    }
}