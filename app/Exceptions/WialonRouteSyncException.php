<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class WialonRouteSyncException extends RuntimeException
{
    public function __construct(string $message, public readonly ?int $routeId = null, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
