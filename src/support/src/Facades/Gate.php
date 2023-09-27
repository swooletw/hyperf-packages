<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Closure;
use SwooleTW\Hyperf\Auth\Access\Gate as AuthGate;
use SwooleTW\Hyperf\Auth\Access\Response;
use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;
use SwooleTW\Hyperf\Auth\Contracts\Gate as GateContract;

/**
 * @method static bool has(string|array $ability)
 * @method static Response allowIf(Response|Closure|bool $condition, ?string $message = null, ?string $code = null)
 * @method static Response denyIf(Response|Closure|bool $condition, ?string $message = null, ?string $code = null)
 * @method static AuthGate define(string $ability, array|callable|string $callback)
 * @method static AuthGate resource(string $name, string $class, ?array $abilities = null)
 * @method static AuthGate policy(string $class, string $policy)
 * @method static AuthGate before(callable $callback)
 * @method static AuthGate after(callable $callback)
 * @method static bool allows(string $ability, mixed $arguments = [])
 * @method static bool denies(string $ability, mixed $arguments = [])
 * @method static bool check(iterable|string $abilities, mixed $arguments = [])
 * @method static bool any(iterable|string $abilities, mixed $arguments = [])
 * @method static bool none(iterable|string $abilities, mixed $arguments = [])
 * @method static Response authorize(string $ability, mixed $arguments = [])
 * @method static Response inspect(string $ability, mixed $arguments = [])
 * @method static mixed raw(string $ability, mixed $arguments = [])
 * @method static mixed|void getPolicyFor(object|string $class)
 * @method static mixed resolvePolicy(string $class)
 * @method static AuthGate forUser(?Authenticatable $user)
 * @method static array abilities()
 * @method static array policies()
 * @method static AuthGate defaultDenialResponse(Response $response)
 * @method static Response denyWithStatus(int $status, ?string $message = null, int|string|null $code = null)
 * @method static Response denyAsNotFound(?string $message = null, int|string|null $code = null)
 *
 * @see AuthGate
 */
class Gate extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GateContract::class;
    }
}
