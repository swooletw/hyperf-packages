<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth\Contracts;

interface FactoryContract
{
    /**
     * Get a guard instance by name.
     *
     * @return \SwooleTW\Hyperf\Auth\Guard|\SwooleTW\Hyperf\Auth\StatefulGuard
     */
    public function guard(?string $name = null): Guard|StatefulGuard;

    /**
     * Set the default guard the factory should serve.
     */
    public function shouldUse(string $name): void;
}
