<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Bootstrap;

use Hyperf\Command\Annotation\Command as AnnotationCommand;
use Hyperf\Command\Command;
use Hyperf\Command\Parser;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\ReflectionManager;
use SwooleTW\Hyperf\Foundation\Command\Console;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Application as ApplicationContract;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel as KernelContract;
use SwooleTW\Hyperf\Foundation\Database\CommandCollector;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RegisterCommands
{
    /**
     * Register App Commands.
     */
    public function bootstrap(ApplicationContract $app): void
    {
        $container = $app->getContainer();

        // Load commands from the given directory.
        $reflections = [];
        if ($container->has(KernelContract::class)) {
            $kernel = $container->get(KernelContract::class);
            $kernel->commands();
            if ($loadPaths = $container->get(KernelContract::class)->getLoadPaths()) {
                $reflections = ReflectionManager::getAllClasses($loadPaths);
            }
        }

        // Load commands from registered closures
        $consoleCommands = [];
        foreach (Console::getCommands() as $handlerId => $command) {
            $handlerId = "commands.{$handlerId}";
            $container->set($handlerId, $command);
            $consoleCommands[] = $handlerId;
        }

        // Load commands from config
        $commands = $container->get(ConfigInterface::class)
            ->get('commands', []);
        $commands = array_merge(
            $commands,
            CommandCollector::getAllCommands(),
            $consoleCommands
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

        $container->get(ConfigInterface::class)
            ->set('commands', array_unique($commands));

        // Register commands to application.
        foreach ($commands as $command) {
            $app->add(
                $this->pendingCommand($container->get($command))
            );
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws SymfonyInvalidArgumentException
     * @throws LogicException
     */
    protected function pendingCommand(SymfonyCommand $command): SymfonyCommand
    {
        /** @var null|AnnotationCommand $annotation */
        $annotation = AnnotationCollector::getClassAnnotation($command::class, AnnotationCommand::class) ?? null;

        if (! $annotation) {
            return $command;
        }

        if ($annotation->signature) {
            [$name, $arguments, $options] = Parser::parse($annotation->signature);
            if ($name) {
                $annotation->name = $name;
            }
            if ($arguments) {
                $annotation->arguments = array_merge($annotation->arguments, $arguments);
            }
            if ($options) {
                $annotation->options = array_merge($annotation->options, $options);
            }
        }

        if ($annotation->name) {
            $command->setName($annotation->name);
        }

        if ($annotation->arguments) {
            $annotation->arguments = array_map(static function ($argument): InputArgument {
                if ($argument instanceof InputArgument) {
                    return $argument;
                }

                if (is_array($argument)) {
                    return new InputArgument(...$argument);
                }

                throw new LogicException(sprintf('Invalid argument type: %s.', gettype($argument)));
            }, $annotation->arguments);

            $command->getDefinition()->addArguments($annotation->arguments);
        }

        if ($annotation->options) {
            $annotation->options = array_map(static function ($option): InputOption {
                if ($option instanceof InputOption) {
                    return $option;
                }

                if (is_array($option)) {
                    return new InputOption(...$option);
                }

                throw new LogicException(sprintf('Invalid option type: %s.', gettype($option)));
            }, $annotation->options);

            $command->getDefinition()->addOptions($annotation->options);
        }

        if ($annotation->description) {
            $command->setDescription($annotation->description);
        }

        if ($annotation->aliases) {
            $command->setAliases($annotation->aliases);
        }

        return $command;
    }
}
