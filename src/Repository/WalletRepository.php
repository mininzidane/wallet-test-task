<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Wallet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wallet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wallet[]    findAll()
 * @method Wallet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    public function getByOwner(User $user, string $walletNumber): ?Wallet
    {
        return $this->createQueryBuilder('w')
            ->innerJoin(User::class, 'u')
            ->where('u = :user')
            ->andWhere('w.number = :number')
            ->setParameter('user', $user)
            ->setParameter('number', $walletNumber)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function checkWalletExists(string $number): bool
    {
        $subQueryQb = $this->createQueryBuilder('w2');
        $subQueryQb
            ->andWhere('w2.number = :number')
            ->andWhere('w2.id = w1.id')
        ;
        $qb = $this->createQueryBuilder('w1');
        $qb
            ->select('1')
            ->where($qb->expr()->exists($subQueryQb->getDQL()))
            ->setParameter('number', $number)
        ;
        return $qb->getQuery()->getOneOrNullResult() !== null;
    }
}
