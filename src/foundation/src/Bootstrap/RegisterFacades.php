<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Bootstrap;

use Hyperf\Contract\ConfigInterface;
use SwooleTW\Hyperf\Foundation\Contracts\Application as ApplicationContract;
use SwooleTW\Hyperf\Support\Facades\Facade;

class RegisterFacades
{
    /**
     * Load Class Aliases.
     */
    public function bootstrap(ApplicationContract $app): void
    {
        Facade::clearResolvedInstances();

        $aliases = $app->get(ConfigInterface::class)
            ->get('app.aliases', []);

        foreach ($aliases as $alias => $class) {
            $this->registerAlias($class, $alias);
        }
    }

    protected function registerAlias(string $class, string $alias): void
    {
        if (class_exists($alias)) {
            return;
        }

        class_alias($class, $alias);
    }
}
