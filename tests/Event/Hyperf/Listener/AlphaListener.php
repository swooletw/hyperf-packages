<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Event\Hyperf\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use SwooleTW\Hyperf\Tests\Event\Hyperf\Event\Alpha;

class AlphaListener implements ListenerInterface
{
    public $value = 1;

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            Alpha::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event): void
    {
        $this->value = 2;
    }
}
