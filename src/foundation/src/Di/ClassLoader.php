<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Di;

use Hyperf\Di\ClassLoader as BaseClassLoader;

class ClassLoader extends BaseClassLoader
{
    protected static function loadDotenv(): void
    {
        DotenvManager::load([BASE_PATH]);
    }
}
