<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Event;

use Closure;
use Hyperf\Collection\Collection;
use Hyperf\Event\ListenerData;
use Hyperf\Stdlib\SplPriorityQueue;
use Hyperf\Stringable\Str;
use Psr\EventDispatcher\ListenerProviderInterface;

use function Hyperf\Collection\collect;

class ListenerProvider implements ListenerProviderInterface
{
    public array $listeners = [];

    public array $wildcards = [];

    public function getListenersForEvent(object|string $event): iterable
    {
        $listeners = $this->getListenersForCondition($this->listeners, function ($listener) use ($event) {
            return is_string($event) ? $event === $listener->event : $event instanceof $listener->event;
        });

        $wildcards = $this->getListenersForCondition($this->wildcards, function ($listener) use ($event) {
            return Str::is($listener->event, $event);
        });

        $queue = new SplPriorityQueue();

        foreach ($listeners->merge($wildcards) as $index => $listener) {
            $queue->insert($listener, $index * -1);
        }

        return $queue;
    }

    public function on(
        array|Closure|string $event,
        null|array|Closure|string $listener = null,
        int $priority = ListenerData::DEFAULT_PRIORITY
    ): void {
        if (is_string($event) && str_contains($event, '*')) {
            $this->wildcards[] = (object) compact('event', 'listener', 'priority');

            return;
        }

        $this->listeners[] = (object) compact('event', 'listener', 'priority');
    }

    public function all(): array
    {
        return $this->listeners;
    }

    public function forget(object|string $event): void
    {
        if (is_string($event) && str_contains($event, '*')) {
            $this->wildcards = array_filter($this->wildcards, function ($listener) use ($event) {
                return $event !== $listener->event;
            });

            return;
        }

        $this->listeners = array_filter($this->listeners, function ($listener) use ($event) {
            return is_string($event) ? $event !== $listener->event : ! $event instanceof $listener->event;
        });
    }

    public function has(object|string $event): bool
    {
        foreach ($this->listeners as $listener) {
            if (is_string($event) ? $event === $listener->event : $event instanceof $listener->event) {
                return true;
            }
        }

        if (! is_string($event)) {
            return false;
        }

        foreach ($this->wildcards as $listener) {
            if ($event === $listener->event) {
                return true;
            }
        }

        return $this->hasWildcard($event);
    }

    public function hasWildcard(string $event): bool
    {
        foreach ($this->wildcards as $listener) {
            if (Str::is($listener->event, $event)) {
                return true;
            }
        }

        return false;
    }

    protected function getListenersForCondition(array $listeners, callable $condition): Collection
    {
        return collect($listeners)
            ->flatMap(function ($listener, $index) use ($condition) {
                if (! $condition($listener)) {
                    return [];
                }

                return [[
                    'listener' => $listener->listener,
                    'priority' => $listener->priority,
                    'index' => $index,
                ]];
            })
            ->sortBy([
                ['priority', 'desc'],
                ['index', 'asc'],
            ])
            ->pluck('listener');
    }
}
