<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT\Contracts;

interface BlacklistContract
{
    /**
     * Add the token (jti claim) to the blacklist.
     *
     * @param  \array  $payload
     * @return bool
     */
    public function add(array $payload): bool;

    /**
     * Add the token (jti claim) to the blacklist indefinitely.
     *
     * @param  \array  $payload
     * @return bool
     */
    public function addForever(array $payload): bool;

    /**
     * Determine whether the token has been blacklisted.
     *
     * @param  \array  $payload
     * @return bool
     */
    public function has(array $payload): bool;

    /**
     * Remove the token (jti claim) from the blacklist.
     *
     * @param  \array  $payload
     * @return bool
     */
    public function remove(array $payload): bool;

    /**
     * Remove all tokens from the blacklist.
     *
     * @return bool
     */
    public function clear(): bool;
}
