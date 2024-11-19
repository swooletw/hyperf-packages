<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Broadcasting;

use SwooleTW\Hyperf\Cache\Contracts\Repository;
use SwooleTW\Hyperf\Foundation\ApplicationContext;

// TODO: wait queue
// use Illuminate\Contracts\Queue\ShouldBeUnique;

// TODO: wait queue
// class UniqueBroadcastEvent extends BroadcastEvent implements ShouldBeUnique
class UniqueBroadcastEvent extends BroadcastEvent
{
    /**
     * The unique lock identifier.
     */
    public mixed $uniqueId;

    /**
     * The number of seconds the unique lock should be maintained.
     */
    public int $uniqueFor;

    /**
     * Create a new event instance.
     */
    public function __construct(mixed $event)
    {
        $this->uniqueId = get_class($event);

        if (method_exists($event, 'uniqueId')) {
            $this->uniqueId .= $event->uniqueId();
        } elseif (property_exists($event, 'uniqueId')) {
            $this->uniqueId .= $event->uniqueId;
        }

        if (method_exists($event, 'uniqueFor')) {
            $this->uniqueFor = $event->uniqueFor();
        } elseif (property_exists($event, 'uniqueFor')) {
            $this->uniqueFor = $event->uniqueFor;
        }

        parent::__construct($event);
    }

    /**
     * Resolve the cache implementation that should manage the event's uniqueness.
     */
    public function uniqueVia(): Repository
    {
        // TODO: Repository 好像沒有註冊在 SwooleTW\Hyperf\Foundation\Application@registerCoreContainerAliases
        return method_exists($this->event, 'uniqueVia')
            ? $this->event->uniqueVia()
            : ApplicationContext::getContainer()->get(Repository::class);
    }
}
