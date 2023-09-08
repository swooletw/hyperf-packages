<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\ObjectPool;

use Hyperf\Coordinator\Timer;

class ConstantFrequency implements LowFrequencyInterface
{
    protected Timer $timer;

    protected ?int $timerId = null;

    protected int $interval = 10;

    public function __construct(protected ?ObjectPool $pool = null)
    {
        $this->timer = new Timer();
        if ($pool) {
            $this->timerId = $this->timer->tick(
                $this->interval,
                fn () => $this->pool->flushOne()
            );
        }
    }

    public function __destruct()
    {
        $this->clear();
    }

    public function clear()
    {
        if ($this->timerId) {
            $this->timer->clear($this->timerId);
        }
        $this->timerId = null;
    }

    public function isLowFrequency(): bool
    {
        return false;
    }
}
