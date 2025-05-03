<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception thrown when unable to generate a unique share code.
 */
class ShareCodeGenerationException extends RuntimeException
{
    /**
     * Create a new exception instance.
     *
     * @param  string  $message  The exception message
     * @param  int  $code  The exception code
     * @param  \Throwable|null  $previous  The previous exception
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
