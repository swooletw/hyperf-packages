<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console;

use Closure;
use Hyperf\Command\Command;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Container\Contracts\Container as ContainerContract;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Application as ApplicationContract;
use SwooleTW\Hyperf\Support\ProcessUtils;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Process\PhpExecutableFinder;

class Application extends SymfonyApplication implements ApplicationContract
{
    /**
     * Indicates if the application has "booted".
     */
    protected bool $booted = false;

    /**
     * The output from the previous command.
     */
    protected ?BufferedOutput $lastOutput;

    /**
     * The console application bootstrappers.
     */
    protected static array $bootstrappers = [];

    /**
     * A map of command names to classes.
     */
    protected array $commandMap = [];

    public function __construct(
        protected ContainerInterface $container,
        protected EventDispatcherInterface $dispatcher,
        string $version
    ) {
        parent::__construct('Laravel Hyperf', $version);

        if ($dispatcher instanceof EventDispatcher) {
            $this->setDispatcher($dispatcher);
            $this->setSignalsToDispatchEvent();
        }

        $this->setAutoExit(false);
        $this->setCatchExceptions(false);

        $this->bootstrap();
    }

    /**
     * Determine the proper PHP executable.
     */
    public static function phpBinary(): string
    {
        return ProcessUtils::escapeArgument(
            (new PhpExecutableFinder())->find(false)
        );
    }

    /**
     * Determine the proper Artisan executable.
     */
    public static function artisanBinary(): string
    {
        return ProcessUtils::escapeArgument(
            defined('ARTISAN_BINARY') ? ARTISAN_BINARY : 'artisan'
        );
    }

    /**
     * Format the given command as a fully-qualified executable command.
     */
    public static function formatCommandString(string $string): string
    {
        return sprintf('%s %s %s', static::phpBinary(), static::artisanBinary(), $string);
    }

    /**
     * Register a console "starting" bootstrapper.
     */
    public static function starting(Closure $callback): void
    {
        static::$bootstrappers[] = $callback;
    }

    /**
     * Bootstrap the console application.
     */
    protected function bootstrap(): void
    {
        foreach (static::$bootstrappers as $bootstrapper) {
            (new $bootstrapper())->bootstrap($this->container);
        }
    }

    /**
     * Clear the console application bootstrappers.
     */
    public static function forgetBootstrappers(): void
    {
        static::$bootstrappers = [];
    }

    /**
     * Run an Artisan console command by name.
     *
     * @throws \Symfony\Component\Console\Exception\CommandNotFoundException
     */
    public function call(string $command, array $parameters = [], ?OutputInterface $outputBuffer = null): int
    {
        [$command, $input] = $this->parseCommand($command, $parameters);

        if (! $this->has($command)) {
            throw new CommandNotFoundException(sprintf('The command "%s" does not exist.', $command));
        }

        return $this->run(
            $input,
            $this->lastOutput = $outputBuffer ?: new BufferedOutput()
        );
    }

    /**
     * Parse the incoming Artisan command and its input.
     */
    protected function parseCommand(string $command, array $parameters): array
    {
        if (is_subclass_of($command, SymfonyCommand::class)) {
            $callingClass = true;

            $command = $this->container->get($command)->getName();
        }

        if (! isset($callingClass) && empty($parameters)) {
            $command = $this->getCommandName($input = new StringInput($command));
        } else {
            array_unshift($parameters, $command);

            $input = new ArrayInput($parameters);
        }

        return [$command, $input];
    }

    /**
     * Get the output for the last run command.
     */
    public function output(): string
    {
        return $this->lastOutput && method_exists($this->lastOutput, 'fetch')
            ? $this->lastOutput->fetch()
            : '';
    }

    /**
     * Add a command, resolving through the application.
     */
    public function resolve(Command|string $command): ?SymfonyCommand
    {
        if (is_subclass_of($command, SymfonyCommand::class) && ($commandName = $command::getDefaultName())) {
            foreach (explode('|', $commandName) as $name) {
                $this->commandMap[$name] = $command;
            }

            return null;
        }

        if ($command instanceof Command) {
            return $this->add($command);
        }

        return $this->add(
            $this->container->get($command)
        );
    }

    /**
     * Resolve an array of commands through the application.
     *
     * @param array|mixed $commands
     * @return $this
     */
    public function resolveCommands($commands): static
    {
        $commands = is_array($commands) ? $commands : func_get_args();

        foreach ($commands as $command) {
            $this->resolve($command);
        }

        return $this;
    }

    /**
     * Set the container command loader for lazy resolution.
     *
     * @return $this
     */
    public function setContainerCommandLoader(): static
    {
        $this->setCommandLoader(
            new ContainerCommandLoader($this->container, $this->commandMap)
        );

        return $this;
    }

    public function getContainer(): ContainerContract
    {
        return $this->container;
    }
}
