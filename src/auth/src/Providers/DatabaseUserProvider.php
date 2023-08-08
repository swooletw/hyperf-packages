<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth\Providers;

use Closure;
use Hyperf\Contract\Arrayable;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Stringable\Str;
use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;
use SwooleTW\Hyperf\Auth\Contracts\UserProvider;
use SwooleTW\Hyperf\Auth\GenericUser;
use SwooleTW\Hyperf\Hashing\Contracts\Hasher as HashContract;

class DatabaseUserProvider implements UserProvider
{
    public function __construct(
        protected ConnectionInterface $connection,
        protected HashContract $hasher,
        protected string $table
    ) {}

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \SwooleTW\Hyperf\Auth\Contracts\Authenticatable|null
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        $user = $this->connection->table($this->table)->find($identifier);

        return $this->getGenericUser($user);
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \SwooleTW\Hyperf\Auth\Contracts\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials) ||
           (count($credentials) === 1 &&
            array_key_exists('password', $credentials))) {
            return null;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // generic "user" object that will be utilized by the Guard instances.
        $query = $this->connection->table($this->table);

        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'password')) {
                continue;
            }

            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } elseif ($value instanceof Closure) {
                $value($query);
            } else {
                $query->where($key, $value);
            }
        }

        // Now we are ready to execute the query to see if we have an user matching
        // the given credentials. If not, we will just return nulls and indicate
        // that there are no matching users for these given credential arrays.
        $user = $query->first();

        return $this->getGenericUser($user);
    }

    /**
     * Get the generic user.
     *
     * @param  mixed  $user
     * @return \SwooleTW\Hyperf\Auth\GenericUser|null
     */
    protected function getGenericUser($user): ?GenericUser
    {
        if (! is_null($user)) {
            return new GenericUser((array) $user);
        }

        return null;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \SwooleTW\Hyperf\Auth\Contracts\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials): ?Authenticatable
    {
        return $this->hasher->check(
            $credentials['password'], $user->getAuthPassword()
        );
    }
}
