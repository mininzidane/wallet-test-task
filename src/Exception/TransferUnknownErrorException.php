<?php

declare(strict_types=1);

namespace App\Exception;

use Throwable;

class TransferUnknownErrorException extends \Exception
{
    public function __construct($message = 'Transfer Unknown Error', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
