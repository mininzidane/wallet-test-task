<?php

namespace App\Entity;

use App\Repository\TransactionLogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TransactionLogRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="wallet_from_id", columns={"wallet_from_id"}),
 *     @ORM\Index(name="wallet_to_id", columns={"wallet_to_id"})
 * })
 */
class TransactionLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Wallet
     * @ORM\ManyToOne(targetEntity="App\Entity\Wallet")
     */
    private $walletFrom;

    /**
     * @var Wallet
     * @ORM\ManyToOne(targetEntity="App\Entity\Wallet")
     */
    private $walletTo;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datetime;

    public function __construct()
    {
        $this->datetime = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWalletFrom(): ?Wallet
    {
        return $this->walletFrom;
    }

    public function setWalletFrom(Wallet $walletFrom): self
    {
        $this->walletFrom = $walletFrom;

        return $this;
    }

    public function getWalletTo(): ?Wallet
    {
        return $this->walletTo;
    }

    public function setWalletTo(Wallet $walletTo): self
    {
        $this->walletTo = $walletTo;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDatetime(): ?\DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTimeInterface $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }
}
