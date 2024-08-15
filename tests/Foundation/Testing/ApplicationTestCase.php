<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Foundation\Testing;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Di\ClassLoader;
use Hyperf\Support\Filesystem\Filesystem;
use Swoole\Runtime;
use Swoole\Timer;
use SwooleTW\Hyperf\Foundation\Application;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel as KernelContract;
use SwooleTW\Hyperf\Foundation\Console\Kernel as ConsoleKernel;
use SwooleTW\Hyperf\Foundation\Contracts\Application as ApplicationContract;
use SwooleTW\Hyperf\Foundation\Testing\TestCase;
use SwooleTW\Hyperf\Foundation\Testing\TestScanHandler;

use function Swoole\Coroutine\run;

/**
 * @internal
 * @coversNothing
 */
class ApplicationTestCase extends TestCase
{
    protected static $hasBootstrappedApplication = false;

    protected function setUp(): void
    {
        if (! static::$hasBootstrappedApplication) {
            $this->bootstrapApplication();
            static::$hasBootstrappedApplication = true;
        }

        $this->afterApplicationCreated(function () {
            Timer::clearAll();
            CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
        });

        run(function () {
            parent::setUp();
        });
    }

    protected function bootstrapApplication(): void
    {
        ini_set('display_errors', 'on');
        ini_set('display_startup_errors', 'on');

        error_reporting(E_ALL);

        ! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 3) . '/tests/Foundation/fixtures/hyperf');
        ! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

        $this->generateComposerLock();

        Runtime::enableCoroutine(true);
        ClassLoader::init(null, null, new TestScanHandler());

        $this->createApplication()
            ->get(ApplicationInterface::class);
    }

    protected function createApplication(): ApplicationContract
    {
        $app = new Application();
        $app->define(KernelContract::class, ConsoleKernel::class);

        ApplicationContext::setContainer($app);

        return $app;
    }

    protected function generateComposerLock(): void
    {
        $content = [
            'packages' => [
                [
                    'name' => 'hyperf-testing',
                    'extra' => [
                        'hyperf' => [
                            'config' => BootstrapConfigProvider::get(),
                        ],
                    ],
                ],
            ],
            'packages-dev' => [],
        ];

        (new Filesystem())->replace(
            BASE_PATH . '/composer.lock',
            json_encode($content, JSON_PRETTY_PRINT)
        );
    }
}
