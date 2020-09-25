<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Exception\InsufficientFundsException;
use App\Exception\TransferUnknownErrorException;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TransactionService
{
    private const COMMISSION = 0.015;

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

    public function transfer(Wallet $walletFrom, Wallet $walletTo, int $amount): void
    {
        $amountWithCommission = (int)\round($amount * (1 + self::COMMISSION), 0, PHP_ROUND_HALF_DOWN);
        $newBalance = $walletFrom->getBalance() - $amountWithCommission;
        if ($newBalance < 0) {
            throw new InsufficientFundsException();
        }

        $this->entityManager->beginTransaction();
        $this->entityManager->lock($walletFrom, LockMode::PESSIMISTIC_WRITE);
        $this->entityManager->lock($walletTo, LockMode::PESSIMISTIC_WRITE);
        try {
            $walletFrom->setBalance($newBalance);
            $walletTo->setBalance($walletTo->getBalance() + $amountWithCommission);
            $transaction = new Transaction();
            $transaction
                ->setAmount(-$amount)
                ->setWalletFrom($walletFrom)
                ->setWalletTo($walletTo)
            ;
            $this->entityManager->persist($transaction);
            $transaction = new Transaction();
            $transaction
                ->setAmount($amount)
                ->setWalletFrom($walletTo)
                ->setWalletTo($walletFrom)
            ;
            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            $this->entityManager->commit();

        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            $this->entityManager->close();
            $this->logger->error($e->getMessage(), $e->getTrace());
            throw new TransferUnknownErrorException();
        }
    }
}
