<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\Contract\SessionInterface;

/**
 * @method static void flash(string $key, mixed $value = true)
 * @method static void now(string $key, mixed $value)
 * @method static void reflash()
 * @method static void keep($keys = null)
 * @method static void flashInput(array $value)
 * @method static void ageFlashData()
 * @method static bool isValidId(string $id)
 * @method static bool start()
 * @method static string getId()
 * @method static void setId(string $id)
 * @method static string getName()
 * @method static void setName(string $name)
 * @method static bool invalidate(?int $lifetime = null)
 * @method static bool migrate(bool $destroy = false, ?int $lifetime = null)
 * @method static void save()
 * @method static bool has(string $name)
 * @method static mixed get(string $name, $default = null)
 * @method static void set(string $name, $value)
 * @method static void put($key, $value = null)
 * @method static array all()
 * @method static void replace(array $attributes)
 * @method static mixed remove(string $name)
 * @method static void forget($keys)
 * @method static void clear()
 * @method static bool isStarted()
 * @method static string token()
 * @method static string regenerateToken()
 * @method static ?string previousUrl()
 * @method static void setPreviousUrl(string $url)
 * @method static void push(string $key, $value)
 */
class Session extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SessionInterface::class;
    }
}
