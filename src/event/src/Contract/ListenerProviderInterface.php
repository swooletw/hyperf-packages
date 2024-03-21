<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Event\Contract;

use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProviderInterface;

interface ListenerProviderInterface extends PsrListenerProviderInterface
{
    public function getListenersForEvent(object|string $event): iterable;

    public function on(string $event, array|callable|string $listener, int $priority): void;

    public function all(): array;

    public function forget(string $event): void;

    public function has(string $event): bool;

    public function hasWildcard(string $event): bool;
}
