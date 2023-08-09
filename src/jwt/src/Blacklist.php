<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT;

use Carbon\Carbon;
use SwooleTW\Hyperf\JWT\Contracts\BlacklistContract;
use SwooleTW\Hyperf\JWT\Contracts\StorageContract;
use SwooleTW\Hyperf\JWT\Exceptions\TokenInvalidException;

class Blacklist implements BlacklistContract
{
    public function __construct(
        protected StorageContract $storage,
        protected int $gracePeriod = 0,
        protected int $refreshTTL = 20160,
        protected string $key = 'jti'
    ) {}

    /**
     * Add the token (jti claim) to the blacklist.
     *
     * @param  \array  $payload
     * @return bool
     */
    public function add(array $payload): bool
    {
        // if there is no exp claim then add the jwt to
        // the blacklist indefinitely
        if (! array_key_exists('exp', $payload)) {
            return $this->addForever($payload);
        }

        // if we have already added this token to the blacklist
        if (! empty($this->storage->get($this->getKey($payload)))) {
            return true;
        }

        $this->storage->add(
            $this->getKey($payload),
            ['valid_until' => $this->getGraceTimestamp()],
            $this->getMinutesUntilExpired($payload)
        );

        return true;
    }

    /**
     * Get the number of minutes until the token expiry.
     *
     * @param  \array  $payload
     * @return int
     */
    protected function getMinutesUntilExpired(array $payload): int
    {
        $exp = $this->timestamp($payload['exp']);
        $iat = $this->timestamp($payload['iat']);

        // get the latter of the two expiration dates and find
        // the number of minutes until the expiration date,
        // plus 1 minute to avoid overlap
        return $exp->max($iat->addMinutes($this->refreshTTL))
            ->addMinute()
            ->diffInRealMinutes();
    }

    /**
     * Add the token (jti claim) to the blacklist indefinitely.
     *
     * @param  \array  $payload
     * @return bool
     */
    public function addForever(array $payload): bool
    {
        $this->storage->forever($this->getKey($payload), 'forever');

        return true;
    }

    /**
     * Determine whether the token has been blacklisted.
     *
     * @param  \array  $payload
     * @return bool
     */
    public function has(array $payload): bool
    {
        $value = $this->storage->get($this->getKey($payload));

        // exit early if the token was blacklisted forever,
        if ($value === 'forever') {
            return true;
        } elseif (! $value) {
            return false;
        }

        // check whether the expiry + grace has past
        return ! $this->timestamp($value['valid_until'])->isFuture();
    }

    /**
     * Remove the token (jti claim) from the blacklist.
     *
     * @param  \array  $payload
     * @return bool
     */
    public function remove(array $payload): bool
    {
        return $this->storage->destroy($this->getKey($payload));
    }

    /**
     * Remove all tokens from the blacklist.
     *
     * @return bool
     */
    public function clear(): bool
    {
        $this->storage->flush();

        return true;
    }

    /**
     * Get the timestamp when the blacklist comes into effect
     * This defaults to immediate (0 seconds).
     *
     * @return int
     */
    protected function getGraceTimestamp(): int
    {
        return Carbon::now()->addSeconds($this->gracePeriod)->getTimestamp();
    }

    /**
     * Set the grace period.
     *
     * @param  int  $gracePeriod
     * @return $this
     */
    public function setGracePeriod(int $gracePeriod): static
    {
        $this->gracePeriod = (int) $gracePeriod;

        return $this;
    }

    /**
     * Get the grace period.
     *
     * @return int
     */
    public function getGracePeriod(): int
    {
        return $this->gracePeriod;
    }

    /**
     * Get the unique key held within the blacklist.
     *
     * @param  \array  $payload
     * @return mixed
     */
    public function getKey(array $payload): mixed
    {
        if (! $key = ($payload[$this->key] ?? null)) {
            throw new TokenInvalidException("Claim `{$this->key}` is missing in payload for blacklist");
        }

        return $key;
    }

    /**
     * Set the unique key held within the blacklist.
     *
     * @param  string  $key
     * @return $this
     */
    public function setKey(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Set the refresh time limit.
     *
     * @param  int  $ttl
     * @return $this
     */
    public function setRefreshTTL(int $ttl): static
    {
        $this->refreshTTL = (int) $ttl;

        return $this;
    }

    /**
     * Get the refresh time limit.
     *
     * @return int
     */
    public function getRefreshTTL(): int
    {
        return $this->refreshTTL;
    }

    protected function timestamp(int $timestamp): Carbon
    {
        return Carbon::createFromTimestamp($timestamp);
    }
}
