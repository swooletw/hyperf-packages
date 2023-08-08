<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth\Contracts;

use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;

interface Guard
{
    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool;

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest(): bool;

    /**
     * Get the currently authenticated user.
     *
     * @return \SwooleTW\Hyperf\Auth\Contracts\Authenticatable|null
     */
    public function user(): ?Authenticatable;

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id(): int|string|null;

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool;

    /**
     * Set the current user.
     *
     * @param  \SwooleTW\Hyperf\Auth\Contracts\Authenticatable  $user
     * @return void
     */
    public function setUser(Authenticatable $user): void;
}
