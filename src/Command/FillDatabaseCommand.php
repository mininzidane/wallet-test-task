<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class FillDatabaseCommand extends Command
{
    protected static $defaultName = 'app:fill-db';

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $userPasswordEncoder,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->em = $em;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new User();
        $user->setApiKey(\md5(\uniqid()));
        $user->setUsername('root');
        $user->setPassword($this->userPasswordEncoder->encodePassword($user, '123456'));
        $wallet = new Wallet();
        $wallet->setBalance(Wallet::SATOSHI_IN_BTC);
        $wallet->setNumber('1234567890');
        $wallet->setTitle('wallet1');
        $wallet->setUser($user);
        $this->em->persist($wallet);

        $user2 = new User();
        $user2->setApiKey(\md5(\uniqid()));
        $user2->setUsername('another');
        $user2->setPassword($this->userPasswordEncoder->encodePassword($user2, '456789'));
        $wallet2 = new Wallet();
        $wallet2->setBalance(Wallet::SATOSHI_IN_BTC);
        $wallet2->setNumber('2345678901');
        $wallet2->setTitle('wallet2');
        $wallet2->setUser($user2);

        $wallet3 = new Wallet();
        $wallet3->setBalance(Wallet::SATOSHI_IN_BTC);
        $wallet3->setNumber('2345678902');
        $wallet3->setTitle('wallet3');
        $wallet3->setUser($user2);

        $this->em->persist($wallet2);
        $this->em->persist($wallet3);

        $this->em->flush();
        $output->writeln('Database filled with sample data');

        return 0;
    }
}
