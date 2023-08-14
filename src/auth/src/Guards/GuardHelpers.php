<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth\Guards;

use SwooleTW\Hyperf\Auth\AuthenticationException;
use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;

/**
 * These methods are typically the same across all guards.
 */
trait GuardHelpers
{
    /**
     * Determine if the current user is authenticated. If not, throw an exception.
     *
     * @throws \SwooleTW\Hyperf\Auth\AuthenticationException
     */
    public function authenticate(): Authenticatable
    {
        if (! is_null($user = $this->user())) {
            return $user;
        }

        throw new AuthenticationException();
    }

    public function guest(?string $token = null): bool
    {
        return ! $this->check($token);
    }

    public function id(): int|string|null
    {
        if ($this->user()) {
            return $this->user()->getAuthIdentifier();
        }

        return null;
    }

    public function check(): bool
    {
        return ! is_null($this->user());
    }

    /**
     * Log the given user ID into the application.
     */
    public function loginUsingId(mixed $id): Authenticatable|bool
    {
        if (! is_null($user = $this->provider->retrieveById($id))) {
            $this->login($user);

            return $user;
        }

        return false;
    }

    /**
     * Validate a user's credentials.
     */
    public function validate(array $credentials = []): bool
    {
        return (bool) $this->attempt($credentials, false);
    }

    /**
     * Determine if the user matches the credentials.
     */
    protected function hasValidCredentials(mixed $user, array $credentials): bool
    {
        if (! $user) {
            return false;
        }

        return $this->provider->validateCredentials($user, $credentials);
    }
}
