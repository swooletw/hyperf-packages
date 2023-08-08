<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Bootstrap;

use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\ReflectionManager;
use Psr\Container\ContainerInterface;

class LoadCommands
{
    /**
     * Load App Commands
     *
     * @param  \Psr\Container\ContainerInterface  $app
     * @return void
     */
    public function bootstrap(ContainerInterface $app): void
    {
        $reflections = ReflectionManager::getAllClasses([
            app_path('Console/Commands')
        ]);

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
