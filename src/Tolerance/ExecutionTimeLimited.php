<?php

namespace Rezzza\RestApiBehatExtension\Tolerance;

use Tolerance\Waiter\Waiter;
use Tolerance\Waiter\StatefulWaiter;

class ExecutionTimeLimited implements Waiter, StatefulWaiter
{
    /**
     * @var Waiter
     */
    private $waiter;

    private $maxExecutionTime;

    private $timeEllapsed;

    public function __construct(Waiter $waiter, $maxExecutionTime)
    {
        $this->waiter = $waiter;
        $this->maxExecutionTime = $maxExecutionTime;
        $this->timeEllapsed = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function wait($seconds = 1)
    {
        $this->timeEllapsed += $seconds;
        if ($this->maxExecutionTime < $this->timeEllapsed) {
            throw MaxExecutionTimeReached::withValue($this->maxExecutionTime);
        }
        $this->waiter->wait($seconds);
    }

    /**
     * {@inheritdoc}
     */
    public function resetState()
    {
        $this->timeEllapsed = 0;
    }
}
