<?php

declare(strict_types=1);

namespace Modules\Foundation\Model;

use Faker\Factory as FakerFactory;
use Hyperf\Contract\ConfigInterface;
use Modules\Foundation\Model\Factory;
use Psr\Container\ContainerInterface;

class FactoryInvoker
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $factory = new Factory(
            FakerFactory::create('en_US')
        );

        if (is_dir($path = database_path('factories') ?: '')) {
            $factory->load($path);
        }

        return $factory;
    }
}
