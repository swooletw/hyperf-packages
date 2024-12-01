<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation;

use Hyperf\Contract\ApplicationInterface;
use SwooleTW\Hyperf\Foundation\Console\ApplicationFactory;
use SwooleTW\Hyperf\Foundation\Console\Commands\ServerReloadCommand;
use SwooleTW\Hyperf\Foundation\Console\Commands\VendorPublishCommand;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Schedule as ScheduleContract;
use SwooleTW\Hyperf\Foundation\Console\Scheduling\Schedule;
use SwooleTW\Hyperf\Foundation\Exceptions\Contracts\ExceptionHandler as ExceptionHandlerContract;
use SwooleTW\Hyperf\Foundation\Exceptions\Handler as ExceptionHandler;
use SwooleTW\Hyperf\Foundation\Listeners\ReloadDotenvAndConfig;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ApplicationInterface::class => ApplicationFactory::class,
                ScheduleContract::class => Schedule::class,
                ExceptionHandlerContract::class => ExceptionHandler::class,
            ],
            'listeners' => [
                ReloadDotenvAndConfig::class,
            ],
            'commands' => [
                ServerReloadCommand::class,
                VendorPublishCommand::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file of foundation.',
                    'source' => __DIR__ . '/../publish/app.php',
                    'destination' => BASE_PATH . '/config/autoload/app.php',
                ],
            ],
        ];
    }
}
