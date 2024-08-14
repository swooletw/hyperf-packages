<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Foundation\Testing;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\DbConnection\Db;
use Mockery as m;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Foundation\Console\Contracts\Kernel as KernelContract;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\InteractsWithConsole;
use SwooleTW\Hyperf\Foundation\Testing\RefreshDatabase;
use SwooleTW\Hyperf\Tests\Foundation\Concerns\HasMockedApplication;

/**
 * @internal
 * @coversNothing
 */
class RefreshDatabaseTest extends ApplicationTestCase
{
    use HasMockedApplication;
    use RefreshDatabase;
    use InteractsWithConsole;

    protected bool $dropViews = false;

    protected bool $seed = false;

    protected ?string $seeder = null;

    public function tearDown(): void
    {
        $this->dropViews = false;
        $this->seed = false;
        $this->seeder = null;
        parent::tearDown();
    }

    public function testRefreshTestDatabaseDefault()
    {
        $kernel = m::mock(KernelContract::class);
        $kernel->shouldReceive('call')
            ->once()
            ->with('migrate:fresh', [
                '--drop-views' => false,
                '--database' => 'default',
                '--seed' => false,
            ])->andReturn(0);
        $kernel->shouldReceive('call')
            ->once()
            ->with('migrate:rollback', [])
            ->andReturn(0);

        $this->app = $this->getApplication([
            ConfigInterface::class => fn () => $this->getConfig(),
            KernelContract::class => fn () => $kernel,
            Db::class => fn () => $this->getMockedDatabase(),
        ]);

        $this->refreshTestDatabase();
    }

    public function testRefreshTestDatabaseWithDropViewsOption()
    {
        $this->dropViews = true;

        $kernel = m::mock(KernelContract::class);
        $kernel->shouldReceive('call')
            ->once()
            ->with('migrate:fresh', [
                '--drop-views' => true,
                '--database' => 'default',
                '--seed' => false,
            ])->andReturn(0);
        $kernel->shouldReceive('call')
            ->once()
            ->with('migrate:rollback', [])
            ->andReturn(0);
        $this->app = $this->getApplication([
            ConfigInterface::class => fn () => $this->getConfig(),
            KernelContract::class => fn () => $kernel,
            Db::class => fn () => $this->getMockedDatabase(),
        ]);

        $this->refreshTestDatabase();
    }

    public function testRefreshTestDatabaseWithSeedOption()
    {
        $this->seed = true;

        $kernel = m::mock(KernelContract::class);
        $kernel->shouldReceive('call')
            ->once()
            ->with('migrate:fresh', [
                '--drop-views' => false,
                '--database' => 'default',
                '--seed' => true,
            ])->andReturn(0);
        $kernel->shouldReceive('call')
            ->once()
            ->with('migrate:rollback', [])
            ->andReturn(0);
        $this->app = $this->getApplication([
            ConfigInterface::class => fn () => $this->getConfig(),
            KernelContract::class => fn () => $kernel,
            Db::class => fn () => $this->getMockedDatabase(),
        ]);

        $this->refreshTestDatabase();
    }

    public function testRefreshTestDatabaseWithSeederOption()
    {
        $this->seeder = 'seeder';

        $kernel = m::mock(KernelContract::class);
        $kernel->shouldReceive('call')
            ->once()
            ->with('migrate:fresh', [
                '--drop-views' => false,
                '--database' => 'default',
                '--seeder' => 'seeder',
            ])->andReturn(0);
        $kernel->shouldReceive('call')
            ->once()
            ->with('migrate:rollback', [])
            ->andReturn(0);
        $this->app = $this->getApplication([
            ConfigInterface::class => fn () => $this->getConfig(),
            KernelContract::class => fn () => $kernel,
            Db::class => fn () => $this->getMockedDatabase(),
        ]);

        $this->refreshTestDatabase();
    }

    protected function getConfig(array $config = []): Config
    {
        return new Config(array_merge([
            'database' => [
                'default' => 'default',
            ],
        ], $config));
    }

    protected function getMockedDatabase(): Db
    {
        $connection = m::mock(ConnectionInterface::class);
        $connection->shouldReceive('getEventDispatcher')
            ->twice()
            ->andReturn($eventDispatcher = m::mock(EventDispatcherInterface::class));
        $connection->shouldReceive('unsetEventDispatcher')
            ->twice();
        $connection->shouldReceive('beginTransaction')
            ->once();
        $connection->shouldReceive('rollback')
            ->once();
        $connection->shouldReceive('setEventDispatcher')
            ->twice()
            ->with($eventDispatcher);

        $db = m::mock(Db::class);
        $db->shouldReceive('connection')
            ->twice()
            ->with(null)
            ->andReturn($connection);

        return $db;
    }
}
