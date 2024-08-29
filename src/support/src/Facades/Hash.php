<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Hashing\Contracts\Hasher;
use SwooleTW\Hyperf\Hashing\HashManager;

/**
 * @method static array info(string $hashedValue)
 * @method static string make(string $value, array $options = [])
 * @method static bool check(string $value, ?string $hashedValue, array $options = [])
 * @method static bool needsRehash(string $hashedValue, array $options = [])
 * @method static bool isHashed(string $value)
 * @method static string getDefaultDriver()
 *
 * @see HashManager
 */
class Hash extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Hasher::class;
    }
}
