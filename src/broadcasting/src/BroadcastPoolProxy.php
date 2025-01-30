<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Broadcasting;

use Hyperf\HttpServer\Contract\RequestInterface;
use SwooleTW\Hyperf\Broadcasting\Contracts\Broadcaster;
use SwooleTW\Hyperf\ObjectPool\PoolProxy;

class BroadcastPoolProxy extends PoolProxy implements Broadcaster
{
    public function auth(RequestInterface $request): mixed
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Return the valid authentication response.
     */
    public function validAuthenticationResponse(RequestInterface $request, mixed $result): mixed
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Broadcast the given event.
     */
    public function broadcast(array $channels, string $event, array $payload = []): void
    {
        $this->__call(__FUNCTION__, func_get_args());
    }
}
