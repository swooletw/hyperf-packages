<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Bootstrap;

use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\ReflectionManager;
use Hyperf\Support\Filesystem\Filesystem;
use Psr\Container\ContainerInterface;

class LoadCommands
{
    /**
     * Load App Commands.
     */
    public function bootstrap(ContainerInterface $app): void
    {
        $pathExisted = $app->get(Filesystem::class)
            ->exists($path = app_path('Console/Commands'));
        if (! $pathExisted) {
            return;
        }

        $reflections = ReflectionManager::getAllClasses([$path]);

        $commands = $app->get(ConfigInterface::class)
            ->get('commands', []);
        foreach ($reflections as $reflection) {
            $command = $reflection->getName();
            if (! is_subclass_of($command, Command::class)) {
                continue;
            }
            $commands[] = $command;
        }

        $app->get(ConfigInterface::class)
            ->set('commands', $commands);
    }
}
