<?php

namespace App\Exceptions;

use Exception;

class InvalidListException extends Exception
{
    public function __construct(string $message = "Invalid list operation")
    {
        parent::__construct($message);
    }
} 