<?php

namespace SwooleTW\Hyperf\Auth\Contracts;

use SwooleTW\Hyperf\Auth\Contracts\Guard;
use SwooleTW\Hyperf\Auth\Contracts\StatefulGuard;

interface FactoryContract
{
    /**
     * Get a guard instance by name.
     *
     * @param  string|null  $name
     * @return \SwooleTW\Hyperf\Auth\Guard|\SwooleTW\Hyperf\Auth\StatefulGuard
     */
    public function guard(?string $name = null): Guard|StatefulGuard;

    /**
     * Set the default guard the factory should serve.
     *
     * @param  string  $name
     * @return void
     */
    public function shouldUse(string $name): void;
}
