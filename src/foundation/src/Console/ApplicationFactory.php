<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Framework\SymfonyEventDispatcher;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ApplicationFactory
{
    public function __invoke(ContainerInterface $container)
    {
        if ($container->has(EventDispatcherInterface::class)) {
            $eventDispatcher = $container->get(EventDispatcherInterface::class);
            $eventDispatcher->dispatch(new BootApplication());
        }

        $config = $container->get(ConfigInterface::class);

        $application = new Application($container, $eventDispatcher);

        if ($config->get('symfony.event.enable', false) && isset($eventDispatcher) && class_exists(SymfonyEventDispatcher::class)) {
            $application->setDispatcher(new SymfonyEventDispatcher($eventDispatcher));
        }

        return $application;
    }
}
