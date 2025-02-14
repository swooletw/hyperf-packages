<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Telescope\Watchers;

use Hyperf\Crontab\Event;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Telescope\IncomingEntry;
use SwooleTW\Hyperf\Telescope\Telescope;

class ScheduleWatcher extends Watcher
{
    /**
     * Register the watcher.
     */
    public function register(ContainerInterface $app): void
    {
        $app->get(EventDispatcherInterface::class)
            ->listen([
                Event\AfterExecute::class,
                Event\FailToExecute::class,
            ], [$this, 'recordCommand']);
    }

    /**
     * Record a scheduled command was executed.
     */
    public function recordCommand(Event\Event $event): void
    {
        if (! Telescope::isRecording()) {
            return;
        }

        Telescope::recordScheduledCommand(IncomingEntry::make([
            'command' => $event->crontab->getName(),
            'description' => $event->crontab->getMemo(),
            'expression' => $event->crontab->getRule(),
            'timezone' => $event->crontab->getTimezone(),
            'user' => '',
            'output' => $this->getEventOutput($event),
        ]));
    }

    /**
     * Get the output for the scheduled event.
     */
    protected function getEventOutput(Event\Event $event): ?string
    {
        if ($event instanceof Event\AfterExecute) {
            return 'success';
        }
        if ($event instanceof Event\FailToExecute) {
            return '[fail]' . (string) $event->getThrowable();
        }

        return null;
    }
}
