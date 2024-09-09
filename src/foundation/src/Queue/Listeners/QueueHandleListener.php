<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Queue\Listeners;

use Hyperf\AsyncQueue\AnnotationJob;
use Hyperf\AsyncQueue\Event\AfterHandle;
use Hyperf\AsyncQueue\Event\BeforeHandle;
use Hyperf\AsyncQueue\Event\Event;
use Hyperf\AsyncQueue\Event\FailedHandle;
use Hyperf\AsyncQueue\Event\RetryHandle;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use SwooleTW\Hyperf\Event\CallQueuedListener;

class QueueHandleListener implements ListenerInterface
{
    public function __construct(
        protected StdoutLoggerInterface $logger
    ) {
    }

    public function listen(): array
    {
        return [
            AfterHandle::class,
            BeforeHandle::class,
            FailedHandle::class,
            RetryHandle::class,
        ];
    }

    public function process(object $event): void
    {
        if (! $event instanceof Event || ! $event->getMessage()->job()) {
            return;
        }

        $job = $event->getMessage()->job();
        $jobClass = $this->getJobClass($job);

        switch (true) {
            case $event instanceof BeforeHandle:
                $this->logger->info(sprintf('Processing %s.', $jobClass));
                break;
            case $event instanceof AfterHandle:
                $this->logger->info(sprintf('Processed %s.', $jobClass));
                break;
            case $event instanceof FailedHandle:
                $this->logger->error(sprintf('Failed %s.', $jobClass));
                $this->logger->error((string) $event->getThrowable());
                break;
            case $event instanceof RetryHandle:
                $this->logger->warning(sprintf('Retried %s.', $jobClass));
                break;
        }
    }

    private function getJobClass(mixed $job): string
    {
        if ($job instanceof CallQueuedListener) {
            return is_string($job->class) ? $job->class : get_class($job->class);
        }

        if ($job instanceof AnnotationJob) {
            return sprintf('Job[%s@%s]', $job->class, $job->method);
        }

        return get_class($job);
    }
}
