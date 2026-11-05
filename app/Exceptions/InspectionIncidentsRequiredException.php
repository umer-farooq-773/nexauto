<?php

namespace App\Exceptions;

use RuntimeException;

class InspectionIncidentsRequiredException extends RuntimeException
{
    public function __construct(public array $evaluation)
    {
        parent::__construct('The following incidents were detected and must be created before this inspection can be submitted.');
    }
}
