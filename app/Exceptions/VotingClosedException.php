<?php

namespace App\Exceptions;

use Exception;

class VotingClosedException extends Exception
{
    public static function forList(int $listId): self
    {
        return new self("Voting is closed for list {$listId}.");
    }
}
