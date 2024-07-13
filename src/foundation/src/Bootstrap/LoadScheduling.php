<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Bootstrap;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\CrontabManager;
use Hyperf\Crontab\Parser;
use Hyperf\Crontab\Schedule as HyperfSchedule;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel as KernelContract;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Schedule as ScheduleContract;
use SwooleTW\Hyperf\Foundation\Console\Scheduling\Schedule;
use SwooleTW\Hyperf\Foundation\Contracts\Application as ApplicationContract;

class LoadScheduling
{
    protected string $environment;
    protected CrontabManager $crontabManager;
    protected Parser $parser;

    /**
     * Load Scheduling.
     */
    public function bootstrap(ApplicationContract $app): void
    {
        if (! $app->has(KernelContract::class)) {
            return;
        }

        $this->environment = (string) $app->make(ConfigInterface::class)
            ->get('app_env', '');
        $this->crontabManager = $app->get(CrontabManager::class);
        $this->parser = $app->get(Parser::class);

        $app->get(KernelContract::class)
            ->schedule($schedule = $app->get(ScheduleContract::class));

        $this->registerCrontabs(
            $schedule->getCommands()
        );
    }

    protected function registerCrontabs(array $crontabs): void
    {
        foreach ($crontabs as $crontab) {
            if (! $crontab instanceof Crontab
                || ! $crontab->isEnable()
                || ! $crontab->runsInEnvironment($this->environment)
            ) {
                continue;
            }

            if (! $crontab->getName()) {
                $callback = $crontab->getCallback();
                $crontab->setName(
                    is_array($callback) && isset($callback['command'])
                        ? $callback['command']
                        : 'No Name'
                );
            }

            if (! $this->isValidCrontab($crontab)) {
                return;
            }

            $this->crontabManager->register($crontab);
        }
    }

    public function isValidCrontab(Crontab $crontab): bool
    {
        return $crontab->getRule() && $crontab->getCallback() && $this->parser->isValid($crontab->getRule());
    }
}
