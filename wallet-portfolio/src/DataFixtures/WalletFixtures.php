<?php

namespace App\DataFixtures;

use App\Entity\Wallet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class WalletFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $wallet = new Wallet();
        $wallet->setBalance('500.00');

        $manager->persist($wallet);
        $manager->flush();
    }
}