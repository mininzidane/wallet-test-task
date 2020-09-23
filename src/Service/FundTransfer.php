<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\TransactionLog;
use App\Entity\Wallet;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class FundTransfer
{
    private const COMMISSION = 0.015;

    public const CODE_SUCCESS = 0;
    public const CODE_ERROR_INSUFFICIENT_FUNDS = 1;
    public const CODE_ERROR_UNKNOWN = 2;

    public const CODE_LABEL_MAP = [
        self::CODE_ERROR_INSUFFICIENT_FUNDS => 'Insufficient funds',
        self::CODE_ERROR_UNKNOWN => 'Unknown error',
    ];

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function transfer(Wallet $walletFrom, Wallet $walletTo, float $amount): int
    {
        $amountWithCommission = $amount * (1 + self::COMMISSION);
        $newBalance = $walletFrom->getBalance() - $amountWithCommission;
        if ($newBalance < 0) {
            return self::CODE_ERROR_INSUFFICIENT_FUNDS;
        }

        $this->entityManager->beginTransaction();
        try {
            $walletFrom->setBalance($newBalance);
            $walletTo->setBalance($walletTo->getBalance() + $amountWithCommission);
            $log = new TransactionLog();
            $log
                ->setAmount($amount)
                ->setWalletFrom($walletFrom)
                ->setWalletTo($walletTo)
            ;
            $this->entityManager->persist($walletFrom);
            $this->entityManager->persist($walletTo);
            $this->entityManager->persist($log);
            $this->entityManager->flush();

            $this->entityManager->commit();

        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            $this->logger->error($e->getMessage(), $e->getTrace());
            return self::CODE_ERROR_UNKNOWN;
        }

        return self::CODE_SUCCESS;
    }
}
