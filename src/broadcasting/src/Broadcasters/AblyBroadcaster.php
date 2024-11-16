<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Broadcasting\Broadcasters;

use Ably\AblyRest;
use Ably\Exceptions\AblyException;
use Ably\Models\Message as AblyMessage;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Stringable\Str;
use SwooleTW\Hyperf\Broadcasting\BroadcastException;
use SwooleTW\Hyperf\HttpMessage\Exceptions\AccessDeniedHttpException;

/**
 * @author Matthew Hall (matthall28@gmail.com)
 * @author Taylor Otwell (taylor@laravel.com)
 */
class AblyBroadcaster extends Broadcaster
{
    /**
     * The AblyRest SDK instance.
     */
    protected AblyRest $ably;

    /**
     * Create a new broadcaster instance.
     */
    public function __construct(AblyRest $ably): void
    {
        $this->ably = $ably;
    }

    /**
     * Authenticate the incoming request for a given channel.
     *
     * @throws AccessDeniedHttpException
     */
    public function auth(RequestInterface $request): mixed
    {
        $originalChannelName = $request->input('channel_name');
        $channelName = $this->normalizeChannelName($originalChannelName);

        if (empty($originalChannelName) 
            || ($this->isGuardedChannel($originalChannelName) && ! $this->retrieveUser($channelName))
        ) {
            throw new AccessDeniedHttpException;
        }

        return parent::verifyUserCanAccessChannel(
            $request, $channelName
        );
    }

    /**
     * Return the valid authentication response.
     */
    public function validAuthenticationResponse(RequestInterface $request, mixed $result): mixed
    {
        $originalChannelName = $request->input('channel_name');
        $socketId = $request->input('socket_id');

        if (str_starts_with($originalChannelName, 'private')) {
            $signature = $this->generateAblySignature($originalChannelName, $socketId);

            return ['auth' => $this->getPublicToken().':'.$signature];
        }

        $channelName = $this->normalizeChannelName($originalChannelName);

        $user = $this->retrieveUser($channelName);

        $broadcastIdentifier = method_exists($user, 'getAuthIdentifierForBroadcasting')
                    ? $user->getAuthIdentifierForBroadcasting()
                    : $user->getAuthIdentifier();

        $signature = $this->generateAblySignature(
            $originalChannelName,
            $socketId,
            $userData = array_filter([
                'user_id' => (string) $broadcastIdentifier,
                'user_info' => $result,
            ])
        );

        return [
            'auth' => $this->getPublicToken().':'.$signature,
            'channel_data' => json_encode($userData),
        ];
    }

    /**
     * Generate the signature needed for Ably authentication headers.
     */
    public function generateAblySignature(string $channelName, string $socketId, array|null $userData = null): string
    {
        return hash_hmac(
            'sha256',
            sprintf('%s:%s%s', $socketId, $channelName, $userData ? ':'.json_encode($userData) : ''),
            $this->getPrivateToken(),
        );
    }

    /**
     * Broadcast the given event.
     *
     * @throws BroadcastException
     */
    public function broadcast(array $channels, string $event, array $payload = []): void
    {
        try {
            foreach ($this->formatChannels($channels) as $channel) {
                $this->ably->channels->get($channel)->publish(
                    $this->buildAblyMessage($event, $payload)
                );
            }
        } catch (AblyException $e) {
            throw new BroadcastException(
                sprintf('Ably error: %s', $e->getMessage())
            );
        }
    }

    /**
     * Build an Ably message object for broadcasting.
     */
    protected function buildAblyMessage(string $event, array $payload = []): AblyMessage
    {
        return tap(new AblyMessage, function ($message) use ($event, $payload) {
            $message->name = $event;
            $message->data = $payload;
            $message->connectionKey = data_get($payload, 'socket');
        });
    }

    /**
     * Return true if the channel is protected by authentication.
     */
    public function isGuardedChannel(string $channel): bool
    {
        return Str::startsWith($channel, ['private-', 'presence-']);
    }

    /**
     * Remove prefix from channel name.
     */
    public function normalizeChannelName(string $channel): string
    {
        if ($this->isGuardedChannel($channel)) {
            return str_starts_with($channel, 'private-')
                        ? Str::replaceFirst('private-', '', $channel)
                        : Str::replaceFirst('presence-', '', $channel);
        }

        return $channel;
    }

    /**
     * Format the channel array into an array of strings.
     */
    protected function formatChannels(array $channels): array
    {
        return array_map(function ($channel) {
            $channel = (string) $channel;

            if (Str::startsWith($channel, ['private-', 'presence-'])) {
                return str_starts_with($channel, 'private-')
                    ? Str::replaceFirst('private-', 'private:', $channel)
                    : Str::replaceFirst('presence-', 'presence:', $channel);
            }

            return 'public:'.$channel;
        }, $channels);
    }

    /**
     * Get the public token value from the Ably key.
     */
    protected function getPublicToken(): string
    {
        return Str::before($this->ably->options->key, ':');
    }

    /**
     * Get the private token value from the Ably key.
     */
    protected function getPrivateToken(): string
    {
        return Str::after($this->ably->options->key, ':');
    }

    /**
     * Get the underlying Ably SDK instance.
     */
    public function getAbly(): AblyRest
    {
        return $this->ably;
    }

    /**
     * Set the underlying Ably SDK instance.
     */
    public function setAbly(AblyRest $ably): void
    {
        $this->ably = $ably;
    }
}
