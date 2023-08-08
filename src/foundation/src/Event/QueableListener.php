<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Event;

use Hyperf\AsyncQueue\Job as HyperfJob;
use Hyperf\Context\ApplicationContext;
use InvalidArgumentException;

class QueableListener extends HyperfJob
{
    public object $event;

    public string $listener;

    public function __construct(object $event, string $listener)
    {
        if (! class_exists($listener)) {
            throw new InvalidArgumentException("Invalid listener class `{$listener}` given");
        }

        $this->event = $event;
        $this->listener = $listener;
    }

    public function handle(): void
    {
        ApplicationContext::getContainer()
            ->get($this->listener)
            ->handle($this->event);
    }
}
