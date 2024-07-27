<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Bootstrap;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\CrontabManager;
use Hyperf\Crontab\Parser;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel as KernelContract;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Schedule as ScheduleContract;
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

        $config = $app->get(ConfigInterface::class);

        $this->environment = (string) $config->get('app.env');
        $this->crontabManager = $app->get(CrontabManager::class);
        $this->parser = $app->get(Parser::class);

        $app->get(KernelContract::class)
            ->schedule($schedule = $app->get(ScheduleContract::class));

        if (! $crontabs = $schedule->getCrontabs()) {
            return;
        }

        $config->set('crontab.enable', true);

        $this->registerCrontabs($crontabs);
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
        $rule = $crontab->getRule();

        return $rule
            && $crontab->getCallback()
            && $this->parser->isValid($rule);
    }
}
