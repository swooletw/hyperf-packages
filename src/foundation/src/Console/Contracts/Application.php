<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console\Contracts;

use SwooleTW\Hyperf\Container\Contracts\Container as ContainerContract;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface Application
{
    public function getContainer(): ContainerContract;

    public function add(Command $command);

    public function run(?InputInterface $input = null, ?OutputInterface $output = null): int;
}
