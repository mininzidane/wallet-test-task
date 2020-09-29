<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\WalletRepository;

class WalletNumberGenerator
{
    private const NUMBER_LENGTH = 16;
    /**
     * @var WalletRepository
     */
    private $walletRepository;

    public function __construct(WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    public function generate(): string
    {
        $number = $this->getRandomInt() . $this->getRandomInt();

        if ($this->walletRepository->checkWalletExists($number)) {
            return $this->generate();
        }

        return $number;
    }

    private function getRandomInt(): string
    {
        $chunkSize = self::NUMBER_LENGTH / 2;
        return \str_pad((string) \random_int(1, (10 ** $chunkSize) - 1), $chunkSize, '0', STR_PAD_LEFT);
    }
}
