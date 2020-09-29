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
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CalculateService
     */
    private $calculateService;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        CalculateService $calculateService
    )
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->calculateService = $calculateService;
    }

    public function transfer(Wallet $walletFrom, Wallet $walletTo, int $amount): void
    {
        $newBalance = $this->calculateService->getDecreasedBalance($amount, $walletFrom->getBalance());
        if ($newBalance < 0) {
            throw new InsufficientFundsException();
        }

        $this->entityManager->beginTransaction();
        $this->entityManager->lock($walletFrom, LockMode::PESSIMISTIC_WRITE);
        $this->entityManager->lock($walletTo, LockMode::PESSIMISTIC_WRITE);
        try {
            $walletFrom->setBalance($newBalance);
            $walletTo->setBalance($this->calculateService->getIncreasedBalance($amount, $walletTo->getBalance()));
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
