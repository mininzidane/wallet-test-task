<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Wallet;
use Doctrine\ORM\EntityManagerInterface;

class WalletNumberGenerator
{
    private const NUMBER_LENGTH = 16;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function generate(): string
    {
        $number = $this->getRandomInt() . $this->getRandomInt();

        if ($this->walletExists($number)) {
            return $this->generate();
        }

        return $number;
    }

    private function walletExists(string $number): bool
    {
        $wallet = $this->entityManager->getRepository(Wallet::class)->findOneBy(['number' => $number]);

        return $wallet !== null;
    }

    private function getRandomInt(): string
    {
        $chunkSize = self::NUMBER_LENGTH / 2;
        return \str_pad((string) \random_int(1, (10 ** $chunkSize) - 1), $chunkSize, '0', STR_PAD_LEFT);
    }
}
