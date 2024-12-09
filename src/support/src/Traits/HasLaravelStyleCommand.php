<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Traits;

use Hyperf\Context\ApplicationContext;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Output\NullOutput;

trait HasLaravelStyleCommand
{
    protected ContainerInterface $app;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);

        $this->app = ApplicationContext::getContainer();
    }

    /**
     * Determine if the given argument is present.
     *
     * @param int|string $name
     * @return bool
     */
    public function hasArgument($name)
    {
        return $this->input->hasArgument($name);
    }

    /**
     * Get the value of a command argument.
     *
     * @param null|string $key
     * @return null|array|string
     */
    public function argument($key = null)
    {
        if (is_null($key)) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get all of the arguments passed to the command.
     *
     * @return array
     */
    public function arguments()
    {
        return $this->argument();
    }

    /**
     * Determine if the given option is present.
     *
     * @param string $name
     * @return bool
     */
    public function hasOption($name)
    {
        return $this->input->hasOption($name);
    }

    /**
     * Get the value of a command option.
     *
     * @param null|string $key
     * @return null|array|bool|string
     */
    public function option($key = null)
    {
        if (is_null($key)) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Get all of the options passed to the command.
     *
     * @return array
     */
    public function options()
    {
        return $this->option();
    }

    /**
     * Call another console command without output.
     */
    public function callSilent(string $command, array $arguments = []): int
    {
        $arguments['command'] = $command;

        return $this->getApplication()->find($command)->run($this->createInputFromArguments($arguments), new NullOutput());
    }
}
