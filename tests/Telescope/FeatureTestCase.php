<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Telescope;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Schema\Blueprint;
use SwooleTW\Hyperf\Cache\Contracts\Factory as CacheFactoryContract;
use SwooleTW\Hyperf\Foundation\Contracts\Application as ApplicationContract;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\RunTestsInCoroutine;
use SwooleTW\Hyperf\Foundation\Testing\RefreshDatabase;
use SwooleTW\Hyperf\Queue\Queue;
use SwooleTW\Hyperf\Support\Environment;
use SwooleTW\Hyperf\Support\Facades\Schema;
use SwooleTW\Hyperf\Telescope\Contracts\EntriesRepository;
use SwooleTW\Hyperf\Telescope\EntryType;
use SwooleTW\Hyperf\Telescope\Http\Middleware\Authorize;
use SwooleTW\Hyperf\Telescope\Storage\DatabaseEntriesRepository;
use SwooleTW\Hyperf\Telescope\Storage\EntryModel;
use SwooleTW\Hyperf\Telescope\Telescope;
use SwooleTW\Hyperf\Telescope\TelescopeApplicationServiceProvider;
use SwooleTW\Hyperf\Telescope\TelescopeServiceProvider;
use SwooleTW\Hyperf\Tests\Foundation\Testing\ApplicationTestCase;

/**
 * @internal
 * @coversNothing
 */
class FeatureTestCase extends ApplicationTestCase
{
    use RefreshDatabase;
    use RunTestsInCoroutine;

    protected bool $migrateRefresh = true;

    protected ?Generator $faker = null;

    protected ?ApplicationContract $app = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(
            EntriesRepository::class,
            fn ($container) => $container->get(DatabaseEntriesRepository::class)
        );
        $this->app->get(ConfigInterface::class)
            ->set('telescope', [
                'enabled' => true,
                'path' => 'telescope',
                'middleware' => [
                    Authorize::class,
                ],
                'defer' => false,
            ]);
        $this->app->get(ConfigInterface::class)
            ->set('cache.default', 'array');
        $this->app->get(ConfigInterface::class)
            ->set('cache.stores.array', [
                'driver' => 'array',
                'serialize' => false,
            ]);
        $this->app->get(CacheFactoryContract::class)
            ->forever('telescope:dump-watcher', true);
        $this->app->get(Environment::class)
            ->set('production');
        $this->app->get(Environment::class)
            ->setDebug(false);
    }

    protected function tearDown(): void
    {
        Telescope::$filterUsing = [];
        Telescope::$filterBatchUsing = [];
        Telescope::$afterRecordingHook = null;
        Telescope::flushWatchers();

        Queue::createPayloadUsing(null);

        parent::tearDown();
    }

    protected function migrateFreshUsing(): array
    {
        return [
            '--seed' => $this->shouldSeed(),
            '--database' => $this->getRefreshConnection(),
            '--realpath' => true,
            '--path' => dirname(__DIR__, 2) . '/src/telescope/database/migrations',
        ];
    }

    protected function startTelescope(): void
    {
        Telescope::start($this->app);
        Telescope::startRecording();
    }

    protected function loadServiceProviders(): void
    {
        (new TelescopeServiceProvider($this->app))
            ->boot();
        (new TelescopeApplicationServiceProvider($this->app))
            ->boot();
    }

    protected function createEntry(array $attributes = []): DummyEntryModel
    {
        return DummyEntryModel::create(array_merge([
            'sequence' => random_int(1, 10000),
            'uuid' => $this->getFaker()->uuid(),
            'batch_id' => $this->getFaker()->uuid(),
            'type' => $this->getFaker()->randomElement([
                EntryType::CACHE,
                EntryType::CLIENT_REQUEST,
                EntryType::COMMAND,
                EntryType::DUMP,
                EntryType::EVENT,
                EntryType::EXCEPTION,
                EntryType::JOB,
                EntryType::LOG,
                EntryType::MAIL,
                EntryType::MODEL,
                EntryType::NOTIFICATION,
                EntryType::QUERY,
                EntryType::REDIS,
                EntryType::REQUEST,
                EntryType::SCHEDULED_TASK,
            ]),
            'content' => [$this->getFaker()->word() => $this->getFaker()->word()],
        ], $attributes));
    }

    protected function getFaker(): Generator
    {
        if ($this->faker) {
            return $this->faker;
        }

        return $this->faker = FakerFactory::create();
    }

    protected function createUsersTable()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    protected function loadTelescopeEntries(): Collection
    {
        $this->terminateTelescope();

        return EntryModel::all();
    }

    public function terminateTelescope(): void
    {
        Telescope::store(
            $this->app->get(EntriesRepository::class)
        );
    }
}

class DummyEntryModel extends EntryModel
{
    protected array $guarded = [];
}
