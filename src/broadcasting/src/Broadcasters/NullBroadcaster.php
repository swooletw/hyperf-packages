<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Broadcasting\Broadcasters;

use Hyperf\HttpServer\Contract\RequestInterface;

class NullBroadcaster extends Broadcaster
{
    /**
     * {@inheritdoc}
     */
    public function auth(RequestInterface $request): mixed
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function validAuthenticationResponse(RequestInterface $request, mixed $result): mixed
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function broadcast(array $channels, string $event, array $payload = []): void
    {
    }
}
