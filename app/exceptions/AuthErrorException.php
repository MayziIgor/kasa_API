<?php

namespace App\Exceptions;

use Exception;

class AuthErrorException extends Exception
{
    public function getErrorCode(): int
    {
        return 4;
    }
}
