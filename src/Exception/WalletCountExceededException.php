<?php

declare(strict_types=1);

namespace App\Exception;

use Throwable;

class WalletCountExceededException extends \Exception
{
    public function __construct($message = 'Too many wallets for user', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
