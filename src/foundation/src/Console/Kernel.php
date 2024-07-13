<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console;

use Closure;
use Exception;
use Hyperf\Collection\Arr;
use Hyperf\Command\Annotation\Command as AnnotationCommand;
use Hyperf\Command\ClosureCommand;
use Hyperf\Command\Command;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\Framework\Event\BootApplication;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Foundation\Command\Console;
use SwooleTW\Hyperf\Foundation\Console\Application as ConsoleApplication;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Application as ApplicationContract;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel as KernelContract;
use SwooleTW\Hyperf\Foundation\Console\Scheduling\Schedule;
use SwooleTW\Hyperf\Foundation\Contracts\Application as ContainerContract;
use SwooleTW\Hyperf\Foundation\Database\CommandCollector;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Kernel implements KernelContract
{
    use HasPendingCommand;

    protected ConsoleApplication $artisan;

    /**
     * The Artisan commands provided by the application.
     */
    protected array $commands = [];

    /**
     * The paths where Artisan commands should be automatically discovered.
     */
    protected array $commandPaths = [];

    /**
     * The paths where Artisan "routes" should be automatically discovered.
     */
    protected array $commandRoutePaths = [];

    /**
     * Indicates if the Closure commands have been loaded.
     */
    protected bool $commandsLoaded = false;

    /**
     * The commands paths that have been "loaded".
     */
    protected array $loadedPaths = [];

    /**
     * The console application bootstrappers.
     */
    protected array $bootstrappers = [
        \SwooleTW\Hyperf\Foundation\Bootstrap\LoadAliases::class,
        \SwooleTW\Hyperf\Foundation\Bootstrap\RegisterProviders::class,
        \SwooleTW\Hyperf\Foundation\Bootstrap\BootProviders::class,
    ];

    public function __construct(
        protected ContainerContract $app,
        protected EventDispatcherInterface $events,
        protected array $loadPaths = []
    ) {
        if (! defined('ARTISAN_BINARY')) {
            define('ARTISAN_BINARY', 'artisan');
        }

        $events->dispatch(new BootApplication());
    }

    /**
     * Bootstrap the application for artisan commands.
     */
    protected function bootstrap(): void
    {
        if (! $this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->bootstrappers());
        }

        if (! $this->commandsLoaded) {
            $this->commands();

            if ($this->shouldDiscoverCommands()) {
                $this->discoverCommands();
            }

            $this->loadCommands();

            // scheduling must be loaded after commands being loaded
            // so that we can register commands to scheduler
            $this->app->bootstrapWith([
                \SwooleTW\Hyperf\Foundation\Bootstrap\LoadScheduling::class,
            ]);

            $this->commandsLoaded = true;
        }
    }

    /**
     * Determine if the kernel should discover commands.
     */
    protected function shouldDiscoverCommands(): bool
    {
        return get_class($this) === __CLASS__;
    }

    /**
     * Discover the commands that should be automatically loaded.
     */
    protected function discoverCommands(): void
    {
        foreach ($this->commandPaths as $path) {
            $this->load($path);
        }

        foreach ($this->commandRoutePaths as $path) {
            if (file_exists($path)) {
                require $path;
            }
        }
    }

    protected function loadCommands(): void
    {
        // Load commands from the given directory.
        $reflections = [];
        if ($loadPaths = $this->getLoadPaths()) {
            $reflections = ReflectionManager::getAllClasses($loadPaths);
        }

        // Load commands from registered closures
        $consoleCommands = [];
        foreach (Console::getCommands() as $handlerId => $command) {
            $handlerId = "commands.{$handlerId}";
            $this->app->set($handlerId, $command);
            $consoleCommands[] = $handlerId;
        }

        // Load commands from config
        $commands = $this->app->get(ConfigInterface::class)
            ->get('commands', []);
        $commands = array_merge(
            $commands,
            CommandCollector::getAllCommands(),
            $consoleCommands,
            $this->commands
        );
        foreach ($reflections as $reflection) {
            $command = $reflection->getName();
            if (! is_subclass_of($command, Command::class)) {
                continue;
            }
            $commands[] = $command;
        }

        // Append commands that defined by annotation.
        $annotationCommands = [];
        if (class_exists(AnnotationCollector::class) && class_exists(AnnotationCommand::class)) {
            $annotationAnnotationCommands = AnnotationCollector::getClassesByAnnotation(Command::class);
            $annotationCommands = array_keys($annotationCommands);
            $commands = array_merge($commands, $annotationCommands);
        }

        // Register commands to application.
        foreach ($commands as $command) {
            $this->registerCommand($command);
        }
    }

    /**
     * Register the given command with the console application.
     */
    public function registerCommand(string $command): void
    {
        $this->getArtisan()->add(
            $this->pendingCommand($this->app->get($command))
        );
    }

    /**
     * Run an Artisan console command by name.
     *
     * @throws \Symfony\Component\Console\Exception\CommandNotFoundException
     */
    public function call(string $command, array $parameters = [], ?OutputInterface $outputBuffer = null): int
    {
        return $this->getArtisan()->call($command, $parameters, $outputBuffer);
    }

    /**
     * Get all of the commands registered with the console.
     */
    public function all(): array
    {
        return $this->getArtisan()->all();
    }

    /**
     * Get the output for the last run command.
     */
    public function output(): string
    {
        return $this->getArtisan()->output();
    }

    /**
     * Define the application's command schedule.
     */
    public function schedule(Schedule $schedule): void {}

    /**
     * Register the commands for the application.
     */
    public function commands(): void {}

    /**
     * Register a Closure based command with the application.
     */
    public function command(string $signature, Closure $callback): ClosureCommand
    {
        return Console::command($signature, $callback);
    }

    /**
     * Add loadPaths in the given directory.
     *
     * @param array|string $paths
     */
    public function load($paths): void
    {
        $paths = array_unique(Arr::wrap($paths));

        $paths = array_filter($paths, function ($path) {
            return is_dir($path);
        });

        if (empty($paths)) {
            return;
        }

        $this->loadPaths = array_values(
            array_unique(array_merge($this->loadPaths, $paths))
        );
    }

    /**
     * Get loadPaths for the application.
     */
    public function getLoadPaths(): array
    {
        return $this->loadPaths;
    }

    /**
     * Set the Artisan commands provided by the application.
     *
     * @return $this
     */
    public function addCommands(array $commands): static
    {
        $this->commands = array_values(
            array_unique(
                array_merge($this->commands, $commands)
            )
        );

        return $this;
    }

    /**
     * Set the paths that should have their Artisan commands automatically discovered.
     *
     * @return $this
     */
    public function addCommandPaths(array $paths): static
    {
        $this->commandPaths = array_values(array_unique(array_merge($this->commandPaths, $paths)));

        return $this;
    }

    /**
     * Set the paths that should have their Artisan "routes" automatically discovered.
     *
     * @return $this
     */
    public function addCommandRoutePaths(array $paths): static
    {
        $this->commandRoutePaths = array_values(array_unique(array_merge($this->commandRoutePaths, $paths)));

        return $this;
    }

    /**
     * Get the bootstrap classes for the application.
     */
    protected function bootstrappers(): array
    {
        return $this->bootstrappers;
    }

    /**
     * Get the Artisan application instance.
     */
    public function getArtisan(): ApplicationContract
    {
        if (isset($this->artisan)) {
            return $this->artisan;
        }

        $this->artisan = (new ConsoleApplication($this->app, $this->events, $this->app->version()))
            ->resolveCommands($this->commands)
            ->setContainerCommandLoader();

        $this->app->instance(ApplicationInterface::class, $this->artisan);

        $this->bootstrap();

        return $this->artisan;
    }

    /**
     * Set the Artisan application instance.
     */
    public function setArtisan(ApplicationContract $artisan): void
    {
        $this->artisan = $artisan;
    }

    /**
     * Runs the current application.
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @throws Exception When running fails. Bypass this when {@link setCatchExceptions()}.
     */
    public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
    {
        return $this->getArtisan()->run($input, $output);
    }
}
