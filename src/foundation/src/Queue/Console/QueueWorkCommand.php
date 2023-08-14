<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Queue\Console;

use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Foundation\Support\Concerns\CanStartServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueueWorkCommand extends Command
{
    use CanStartServer;

    public function __construct(
        protected ContainerInterface $container
    ) {
        parent::__construct('queue:work');
    }

    public function configure()
    {
        parent::configure();
        $this->addOption('port', null, InputOption::VALUE_OPTIONAL, 'port to listen on HTTP server, default is 9601');
        $this->setDescription('Start processing jobs on the queue as a daemon');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Listening queue jobs...');

        $this->setProcesses([
            \Hyperf\AsyncQueue\Process\ConsumerProcess::class,
        ]);

        $this->runServer((int) $input->getOption('port'));

        return 0;
    }
}
