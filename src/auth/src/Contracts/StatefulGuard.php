<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth\Contracts;

use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;
use SwooleTW\Hyperf\Auth\Contracts\Guard;

interface StatefulGuard extends Guard
{
    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function attempt(array $credentials = []): bool;

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function once(array $credentials = []): bool;

    /**
     * Log a user into the application.
     *
     * @param  \SwooleTW\Hyperf\Auth\Contracts\Authenticatable  $user
     * @return void
     */
    public function login(Authenticatable $user): void;

    /**
     * Log the given user ID into the application.
     *
     * @param  mixed  $id
     * @return \SwooleTW\Hyperf\Auth\Contracts\Authenticatable|bool
     */
    public function loginUsingId(mixed $id): Authenticatable|bool;

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param  mixed  $id
     * @return \SwooleTW\Hyperf\Auth\Contracts\Authenticatable|bool
     */
    public function onceUsingId(mixed $id): Authenticatable|bool;

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout(): void;
}
