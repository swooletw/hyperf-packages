<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Telescope\Watchers;

use Exception;
use Hyperf\Contract\ConfigInterface;
use Mockery as m;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Bus\Batch;
use SwooleTW\Hyperf\Bus\Contracts\BatchRepository;
use SwooleTW\Hyperf\Bus\Dispatchable;
use SwooleTW\Hyperf\Queue\Contracts\ShouldQueue;
use SwooleTW\Hyperf\Queue\Events\JobFailed;
use SwooleTW\Hyperf\Queue\Events\JobProcessed;
use SwooleTW\Hyperf\Queue\Jobs\FakeJob;
use SwooleTW\Hyperf\Support\Facades\Bus;
use SwooleTW\Hyperf\Telescope\EntryType;
use SwooleTW\Hyperf\Telescope\Jobs\ProcessPendingUpdates;
use SwooleTW\Hyperf\Telescope\Watchers\JobWatcher;
use SwooleTW\Hyperf\Tests\Telescope\FeatureTestCase;

/**
 * @internal
 * @coversNothing
 */
class JobWatcherTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->get(ConfigInterface::class)
            ->set('telescope.watchers', [
                JobWatcher::class => true,
            ]);

        $this->startTelescope();
    }

    public function testJobRegistersProcessingEntry()
    {
        $batch = m::mock(Batch::class);
        $batch->shouldReceive('toArray')
            ->once()
            ->andReturn(['foo' => 'bar']);
        $batchRepository = m::mock(BatchRepository::class);
        $batchRepository->shouldReceive('find')
            ->with('batch-id')
            ->once()
            ->andReturn($batch);

        $this->app->set(BatchRepository::class, $batchRepository);

        MockedBatchableJob::dispatch('batch-id');

        $entry = $this->loadTelescopeEntries()->first();

        $this->assertSame(EntryType::JOB, $entry->type);
        $this->assertSame('batch-id', $entry->family_hash);
        $this->assertSame('processed', $entry->content['status']);
        $this->assertSame('sync', $entry->content['connection']);
        $this->assertSame('default', $entry->content['queue']);
        $this->assertSame(MockedBatchableJob::class, $entry->content['name']);
    }

    public function testJobRegistersEntry()
    {
        Bus::fake();

        $this->app->get(EventDispatcherInterface::class)
            ->dispatch(
                new JobProcessed(
                    'connection',
                    new FakeJob([
                        'telescope_uuid' => 'uuid',
                    ])
                )
            );

        $this->loadTelescopeEntries();

        Bus::assertDispatched(ProcessPendingUpdates::class, function ($job) {
            $entry = $job->pendingUpdates->first();
            $this->assertSame(EntryType::JOB, $entry->type);
            $this->assertSame('processed', $entry->changes['status']);

            return true;
        });
    }

    public function testFailedJobsRegisterEntry()
    {
        Bus::fake();

        $this->app->get(EventDispatcherInterface::class)
            ->dispatch(
                new JobFailed(
                    'connection',
                    new FakeJob([
                        'telescope_uuid' => 'uuid',
                    ]),
                    new Exception($message = 'I never watched Star Wars.')
                )
            );

        $this->loadTelescopeEntries();

        Bus::assertDispatched(ProcessPendingUpdates::class, function ($job) use ($message) {
            $entry = $job->pendingUpdates->first();
            $this->assertSame(EntryType::JOB, $entry->type);
            $this->assertSame('failed', $entry->changes['status']);
            $this->assertSame($message, $entry->changes['exception']['message']);

            return true;
        });
    }
}

class MockedBatchableJob implements ShouldQueue
{
    use Dispatchable;

    public $batchId;

    public function __construct($batchId)
    {
        $this->batchId = $batchId;
    }

    public function handle()
    {
    }
}
