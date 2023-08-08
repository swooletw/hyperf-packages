<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Queue\Listeners;

use Hyperf\AsyncQueue\AnnotationJob;
use Hyperf\AsyncQueue\Event\AfterHandle;
use Hyperf\AsyncQueue\Event\BeforeHandle;
use Hyperf\AsyncQueue\Event\Event;
use Hyperf\AsyncQueue\Event\FailedHandle;
use Hyperf\AsyncQueue\Event\RetryHandle;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use SwooleTW\Hyperf\Foundation\Event\QueableListener;

class QueueHandleListener implements ListenerInterface
{
    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerInterface::class)
            ->channel('stdout');
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
        $isQueable = $job instanceof QueableListener;
        $jobClass = $isQueable ? $job->listener : get_class($job);
        if ($job instanceof AnnotationJob) {
            $jobClass = sprintf('Job[%s@%s]', $job->class, $job->method);
        }

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
}
