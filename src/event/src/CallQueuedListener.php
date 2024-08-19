<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Event;

use Hyperf\AsyncQueue\Job;
use Hyperf\Context\ApplicationContext;
use Throwable;

class CallQueuedListener extends Job
{
    public function __construct(
        public object|string $class,
        public string $method,
        public array $data
    ) {
    }

    public function handle(): void
    {
        $this->getEventHandler()->{$this->method}(...array_values($this->data));
    }

    public function fail(Throwable $e): void
    {
        if (method_exists($this->class, 'failed')) {
            $this->getEventHandler()->failed($e, ...array_values($this->data));
        }
    }

    protected function getEventHandler(): object
    {
        return is_string($this->class) ? ApplicationContext::getContainer()->get($this->class) : $this->class;
    }
}
