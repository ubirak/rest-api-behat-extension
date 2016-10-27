<?php

namespace Rezzza\RestApiBehatExtension\Tolerance;

use Tolerance\Waiter\WaiterException;

class MaxExecutionTimeReached extends WaiterException
{
    public static function withValue($maxExecutionTime)
    {
        return new static(sprintf('Max execution "%s seconds" time is reached', $maxExecutionTime));
    }
}
