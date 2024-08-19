<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Auth\Stub;

use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;

class AccessGateTestAuthenticatable implements Authenticatable
{
    public function __construct(private bool $isAdmin = false)
    {
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): mixed
    {
        return 1;
    }

    public function getAuthPassword(): string
    {
        return 'dummy';
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }
}
