<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Telescope\Storage;

use SwooleTW\Hyperf\Telescope\EntryUpdate;
use SwooleTW\Hyperf\Telescope\Storage\DatabaseEntriesRepository;
use SwooleTW\Hyperf\Tests\Telescope\FeatureTestCase;

/**
 * @internal
 * @coversNothing
 */
class DatabaseEntriesRepositoryTest extends FeatureTestCase
{
    public function testFindEntryByUuid()
    {
        $entry = $this->createEntry();

        $result = $this->app
            ->get(DatabaseEntriesRepository::class)
            ->find($entry->uuid)
            ->jsonSerialize();

        $this->assertSame($entry->uuid, $result['id']);
        $this->assertSame($entry->batch_id, $result['batch_id']);
        $this->assertSame($entry->type, $result['type']);
        $this->assertSame($entry->content, $result['content']);

        $this->assertNull($result['sequence']);
    }

    public function testUpdate()
    {
        $entry = $this->createEntry();

        $repository = $this->app->get(DatabaseEntriesRepository::class);

        $result = $repository
            ->find($entry->uuid)
            ->jsonSerialize();

        $failedUpdates = $repository->update(collect([
            new EntryUpdate($result['id'], $result['type'], ['content' => ['foo' => 'bar']]),
            new EntryUpdate('missing-id', $result['type'], ['content' => ['foo' => 'bar']]),
        ]));

        $this->assertCount(1, $failedUpdates);
        $this->assertSame('missing-id', $failedUpdates->first()->uuid);
    }
}
