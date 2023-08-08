<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth\Contracts;

use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;

interface UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \SwooleTW\Hyperf\Auth\Contracts\Authenticatable|null
     */
    public function retrieveById($identifier): ?Authenticatable;

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \SwooleTW\Hyperf\Auth\Contracts\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable;

    /**
     * Validate a user against the given credentials.
     *
     * @param  \SwooleTW\Hyperf\Auth\Contracts\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials);
}
