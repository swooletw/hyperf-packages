<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth\Contracts;

interface Authenticatable
{
    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName(): string;

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier(): mixed;

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword(): string;
}
