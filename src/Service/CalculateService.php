<?php

declare(strict_types=1);

namespace App\Service;

class CalculateService
{
    private const COMMISSION = 0.015;

    public function getDecreasedBalance(int $amount, int $balance): int
    {
        return $balance - $this->calculateAmountWithCommission($amount);
    }

    public function getIncreasedBalance(int $amount, int $balance): int
    {
        return $balance + $this->calculateAmountWithCommission($amount);
    }

    private function calculateAmountWithCommission(int $amount): int
    {
        return (int)\round($amount * (1 + self::COMMISSION), 0, PHP_ROUND_HALF_DOWN);
    }
}
