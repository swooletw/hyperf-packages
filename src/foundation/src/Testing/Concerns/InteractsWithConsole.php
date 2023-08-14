<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Testing\Concerns;

use Hyperf\Contract\ApplicationInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;

trait InteractsWithConsole
{
    /**
     * Indicates if the console output should be mocked.
     */
    public bool $mockConsoleOutput = true;

    /**
     * Call hyperf command and return code.
     */
    public function command(string $command, array $parameters = []): int
    {
        $application = $this->app->get(ApplicationInterface::class);

        $input = new ArrayInput(array_merge(
            $parameters,
            ['command' => $command]
        ));
        $output = $this->mockConsoleOutput
            ? new NullOutput()
            : new ConsoleOutput();

        $application->setAutoExit(false);

        return $application->run($input, $output);
    }

    /**
     * Disable mocking the console output.
     *
     * @return $this
     */
    protected function withoutMockingConsoleOutput(): static
    {
        $this->mockConsoleOutput = false;

        return $this;
    }
}
