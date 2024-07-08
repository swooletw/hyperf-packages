<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Bootstrap;

use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\ReflectionManager;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel as KernelContract;
use SwooleTW\Hyperf\Foundation\Database\CommandCollector;

class LoadCommands
{
    /**
     * Load App Commands.
     */
    public function bootstrap(ContainerInterface $app): void
    {
        // Load commands from the given directory.
        $reflections = [];
        if ($app->has(KernelContract::class)) {
            $kernel = $app->get(KernelContract::class);
            $kernel->commands();

            $reflections = ReflectionManager::getAllClasses(
                $app->get(KernelContract::class)
                    ->getLoadPaths()
            );
        }

        // Load commands from config
        $commands = $app->get(ConfigInterface::class)
            ->get('commands', []);
        $commands = array_merge($commands, CommandCollector::getAllCommands());
        foreach ($reflections as $reflection) {
            $command = $reflection->getName();
            if (! is_subclass_of($command, Command::class)) {
                continue;
            }
            $commands[] = $command;
        }

        $app->get(ConfigInterface::class)
            ->set('commands', array_unique($commands));
    }
}
