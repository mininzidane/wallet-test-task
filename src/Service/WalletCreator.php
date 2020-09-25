<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\ORM\EntityManagerInterface;

class WalletCreator
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var WalletNumberGenerator
     */
    private $walletNumberGenerator;

    public function __construct(EntityManagerInterface $entityManager, WalletNumberGenerator $walletNumberGenerator)
    {
        $this->entityManager = $entityManager;
        $this->walletNumberGenerator = $walletNumberGenerator;
    }

    public function create(User $user, string $title): ?Wallet
    {
        if ($user->getWallets()->count() >= User::WALLETS_PER_USER) {
            return null;
        }

        $wallet = new Wallet();
        $wallet->setUser($user);
        $wallet->setTitle($title);
        $wallet->setBalance(Wallet::SATOSHI_IN_BTC);
        $wallet->setNumber($this->walletNumberGenerator->generate());
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();

        return $wallet;
    }
}
