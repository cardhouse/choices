<?php

namespace App\Exceptions;

use Exception;

class InvalidShareCodeException extends Exception
{
    public static function notFound(): self
    {
        return new self('That code is invalid or no longer active.');
    }
}
