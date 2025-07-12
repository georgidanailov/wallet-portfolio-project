<?php

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WalletRepository::class)]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $balance = '0.00';

    /**
     * @var Collection<int, TransactionLog>
     */
    #[ORM\OneToMany(targetEntity: TransactionLog::class, mappedBy: 'wallet')]
    private Collection $transactionLogs;

    public function __construct()
    {
        $this->transactionLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(string $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @return Collection<int, TransactionLog>
     */
    public function getTransactionLogs(): Collection
    {
        return $this->transactionLogs;
    }

    public function addTransactionLog(TransactionLog $transactionLog): static
    {
        if (!$this->transactionLogs->contains($transactionLog)) {
            $this->transactionLogs->add($transactionLog);
            $transactionLog->setWallet($this);
        }

        return $this;
    }

    public function removeTransactionLog(TransactionLog $transactionLog): static
    {
        if ($this->transactionLogs->removeElement($transactionLog)) {
            // set the owning side to null (unless already changed)
            if ($transactionLog->getWallet() === $this) {
                $transactionLog->setWallet(null);
            }
        }

        return $this;
    }
}
