<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console\Contracts;

use SwooleTW\Hyperf\Container\Contracts\Container as ContainerContract;
use Symfony\Component\Console\Command\Command;

interface Application
{
    public function getContainer(): ContainerContract;

    public function add(Command $command);
}
