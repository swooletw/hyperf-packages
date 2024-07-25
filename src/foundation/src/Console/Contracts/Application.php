<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console\Contracts;

use Hyperf\Command\Command;
use SwooleTW\Hyperf\Container\Contracts\Container as ContainerContract;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface Application
{
    public function getContainer(): ContainerContract;

    public function add(Command $command);

    public function all(?string $namespace = null);

    public function run(?InputInterface $input = null, ?OutputInterface $output = null);

    public function call(string $command, array $parameters = [], ?OutputInterface $outputBuffer = null): int;

    public function output(): string;

    public function resolve(Command|string $command): ?SymfonyCommand;

    public function resolveCommands($commands): static;

    public function setContainerCommandLoader(): static;
}
