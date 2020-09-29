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

    public function getByNumber(string $walletNumber, ?User $user = null): ?Wallet
    {
        $qb = $this
            ->createQueryBuilder('w')
            ->andWhere('w.number = :number')
            ->setParameter('number', $walletNumber)
        ;
        if ($user !== null) {
            $qb
                ->innerJoin(User::class, 'u')
                ->andWhere('u = :user')
                ->setParameter('user', $user)
            ;
        }
        return $qb->getQuery()->getOneOrNullResult();
    }
}
