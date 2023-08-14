<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Bootstrap;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class LoadAliases
{
    /**
     * Load Class Aliases.
     */
    public function bootstrap(ContainerInterface $app): void
    {
        $aliases = $app->get(ConfigInterface::class)
            ->get('app.aliases', []);
        foreach ($aliases as $alias => $class) {
            if (class_exists($alias)) {
                continue;
            }
            class_alias($class, $alias);
        }
    }
}
