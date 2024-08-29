<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Closure;
use Hyperf\Contract\SessionInterface;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Auth\AuthManager;
use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;
use SwooleTW\Hyperf\Auth\Contracts\Guard;
use SwooleTW\Hyperf\Auth\Contracts\StatefulGuard;
use SwooleTW\Hyperf\Auth\Contracts\UserProvider;
use SwooleTW\Hyperf\Auth\Events\Authenticated;
use SwooleTW\Hyperf\Cookie\CookieJar;

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
 * @method static void logoutOtherDevices(string $password, string $attribute = 'password')
 * @method static bool viaRemember()
 * @method static void rememberUser(Authenticatable $user)
 * @method static void forgetUser()
 * @method static UserProvider|null getProvider()
 * @method static void setProvider(UserProvider $provider)
 * @method static string getName()
 * @method static string getRecallerName()
 * @method static SessionInterface getSession()
 * @method static void setSession(SessionInterface $session)
 * @method static string|null getLastAttempted()
 * @method static bool hasUser()
 * @method static string getCookieJar()
 * @method static void setCookieJar(CookieJar $cookie)
 * @method static Authenticated fired(Authenticatable $user)
 * @method static AuthManager getAuthManager()
 * @method static void setAuthManager(AuthManager $auth)
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
