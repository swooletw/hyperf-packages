<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Telescope\Watchers;

use Hyperf\Crontab\Event;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Telescope\Contracts\EntriesRepository;
use SwooleTW\Hyperf\Telescope\IncomingEntry;
use SwooleTW\Hyperf\Telescope\Telescope;

class ScheduleWatcher extends Watcher
{
    /**
     * The entries repository.
     */
    protected ?EntriesRepository $entriesRepository = null;

    /**
     * Register the watcher.
     */
    public function register(ContainerInterface $app): void
    {
        if (! in_array($_SERVER['argv'][1] ?? null, ['crontab:run', 'schedule:run'])) {
            return;
        }

        $this->entriesRepository = $app->get(EntriesRepository::class);

        Telescope::startRecording();

        $app->get(EventDispatcherInterface::class)
            ->listen([
                Event\BeforeExecute::class,
                Event\AfterExecute::class,
                Event\FailToExecute::class,
            ], [$this, 'recordCommand']);
    }

    /**
     * Record a scheduled command was executed.
     */
    public function recordCommand(Event\Event $event): void
    {
        if ($event instanceof Event\BeforeExecute) {
            Telescope::startRecording();
            return;
        }

        if (! Telescope::isRecording()) {
            return;
        }

        Telescope::recordScheduledCommand(IncomingEntry::make([
            'command' => in_array($event->crontab->getType(), ['closure', 'callback'])
                ? 'Closure'
                : $event->crontab->getName(),
            'description' => $event->crontab->getMemo(),
            'expression' => $event->crontab->getRule(),
            'timezone' => $event->crontab->getTimezone(),
            'user' => '',
            'output' => $this->getEventOutput($event),
        ]));

        Telescope::store($this->entriesRepository);
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
