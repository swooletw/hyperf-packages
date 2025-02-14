<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Closure;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Auth\AuthManager;
use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;
use SwooleTW\Hyperf\Auth\Contracts\Guard;
use SwooleTW\Hyperf\Auth\Contracts\StatefulGuard;
use SwooleTW\Hyperf\Auth\Contracts\UserProvider;

/**
 * @method static Guard|StatefulGuard guard(string $name = null)
 * @method static void shouldUse(string $name)
 * @method static string getDefaultDriver()
 * @method static void setDefaultDriver(string $name)
 * @method static AuthManager extend(string $driver, Closure $callback)
 * @method static AuthManager provider(string $name, Closure $callback)
 * @method static Closure userResolver()
 * @method static AuthManager resolveUsersUsing(Closure $userResolver)
 * @method static array getGuards()
 * @method static AuthManager setApplication(ContainerInterface $app)
 * @method static bool check()
 * @method static bool guest()
 * @method static Authenticatable|null user()
 * @method static int|string|null id()
 * @method static bool validate(array $credentials = [])
 * @method static void setUser(Authenticatable $user)
 * @method static bool attempt(array $credentials = [], bool $remember = false)
 * @method static bool once(array $credentials = [])
 * @method static void login(Authenticatable $user, bool $remember = false)
 * @method static Authenticatable loginUsingId(mixed $id, bool $remember = false)
 * @method static bool onceUsingId(mixed $id)
 * @method static void logout()
 * @method static UserProvider|null getProvider()
 * @method static void setProvider(UserProvider $provider)
 * @method static string getName()
 * @method static bool hasUser()
 *
 * @see AuthManager
 * @see Guard
 * @see StatefulGuard
 */
class Auth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return AuthManager::class;
    }
}
