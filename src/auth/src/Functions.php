<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth;

use Hyperf\Context\ApplicationContext;
use SwooleTW\Hyperf\Auth\Contracts\FactoryContract;
use SwooleTW\Hyperf\Auth\Contracts\Guard;

/**
 * Get auth guard or auth manager.
 */
function auth(?string $guard = null): FactoryContract|Guard
{
    $auth = ApplicationContext::getContainer()
        ->get(AuthManager::class);

    if (is_null($guard)) {
        return $auth;
    }

    return $auth->guard($guard);
}
