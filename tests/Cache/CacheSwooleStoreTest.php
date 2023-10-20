<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Cache;

use Carbon\Carbon;
use Hyperf\Stringable\Str;
use Mockery as m;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Cache\SwooleStore;
use SwooleTW\Hyperf\Cache\SwooleTableManager;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CacheSwooleStoreTest extends TestCase
{
    public function testCanRetrieveItemsFromStore()
    {
        $table = $this->createSwooleTable();

        $table->set('foo', ['value' => serialize('bar'), 'expiration' => time() + 100]);

        $store = new SwooleStore($table);

        $this->assertEquals('bar', $store->get('foo'));
    }

    public function testMissingItemsReturnNull()
    {
        $table = $this->createSwooleTable();

        $store = new SwooleStore($table);

        $this->assertNull($store->get('foo'));
    }

    public function testExpiredItemsReturnNull()
    {
        $table = $this->createSwooleTable();

        $store = new SwooleStore($table);

        $table->set('foo', ['value' => serialize('bar'), 'expiration' => time() - 100]);

        $this->assertNull($store->get('foo'));
    }

    public function testGetMethodCanResolvePendingInterval()
    {
        $table = $this->createSwooleTable();

        $store = new SwooleStore($table);

        $store->interval('foo', fn () => 'bar', 1);

        $this->assertEquals('bar', $store->get('foo'));
    }

    public function testManyMethodCanReturnManyValues()
    {
        $table = $this->createSwooleTable();

        $table->set('foo', ['value' => serialize('bar'), 'expiration' => time() + 100]);
        $table->set('bar', ['value' => serialize('baz'), 'expiration' => time() + 100]);

        $store = new SwooleStore($table);

        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz'], $store->many(['foo', 'bar']));
    }

    public function testPutStoresValueInTable()
    {
        $table = $this->createSwooleTable();

        $store = new SwooleStore($table);

        $store->put('foo', 'bar', 5);

        $this->assertEquals('bar', $store->get('foo'));
    }

    public function testPutManyStoresValueInTable()
    {
        $table = $this->createSwooleTable();

        $store = new SwooleStore($table);

        $store->putMany(['foo' => 'bar', 'bar' => 'baz'], 5);

        $this->assertEquals('bar', $store->get('foo'));
        $this->assertEquals('baz', $store->get('bar'));
    }

    public function testIncrementAndDecrementOperations()
    {
        $table = $this->createSwooleTable();

        $store = new SwooleStore($table);

        $store->increment('counter');
        $this->assertEquals(1, $store->get('counter'));

        $store->increment('counter', 2);
        $this->assertEquals(3, $store->get('counter'));

        $store->decrement('counter', 2);
        $this->assertEquals(1, $store->get('counter'));
    }

    public function testForeverStoresValueInTable()
    {
        $table = $this->createSwooleTable();

        $store = new SwooleStore($table);

        $store->forever('foo', 'bar');

        $this->assertEquals('bar', $store->get('foo'));
    }

    public function testIntervalsCanBeRefreshed()
    {
        $table = $this->createSwooleTable();

        $store = new SwooleStore($table);

        $store->interval('foo', fn () => Str::random(10), 1);

        $this->assertTrue(is_string($first = $store->get('foo')));

        Carbon::setTestNow(now()->addMinutes(1));

        $store->refreshIntervalCaches();

        $this->assertTrue(is_string($second = $store->get('foo')));
        $this->assertNotEquals($first, $second);

        Carbon::setTestNow();
    }

    public function testCanForgetCacheItems()
    {
        $table = $this->createSwooleTable();

        $store = new SwooleStore($table);

        $store->put('foo', 'bar', 5);
        $this->assertTrue($store->forget('foo'));

        $this->assertNull($store->get('foo'));

        $store->put('foo', 'bar', 5);
        $this->assertTrue($store->flush());

        $this->assertNull($store->get('foo'));
    }

    public function testIntervalsAreNotFlushed()
    {
        $table = $this->createSwooleTable();

        $store = new SwooleStore($table);

        $store->interval('foo', fn () => 'bar', 1);
        $this->assertTrue($store->flush());

        $this->assertEquals('bar', $store->get('foo'));
    }

    public function testExpiredAtWithMicrosecond()
    {
        $table = $this->createSwooleTable();

        $store = new SwooleStore($table);

        Carbon::setTestNow('2000-01-01 00:00:00.500000');
        $store->put('foo', 'bar', 1);

        Carbon::setTestNow('2000-01-01 00:00:01.499999');
        $this->assertSame('bar', $store->get('foo'));

        Carbon::setTestNow('2000-01-01 00:00:01.500000');
        $this->assertNull($store->get('foo'));
    }

    public function testCanRemoveExpiredRecordFromTable()
    {
        $table = $this->createSwooleTable();

        $table->set('foo', ['value' => serialize('bar'), 'expiration' => time() - 100]);

        $store = new SwooleStore($table);

        $this->assertNull($store->get('foo'));
        $this->assertFalse($table->get('foo'));
    }

    public function testAutoRemoveRecordWhenTableIsfull()
    {
        $table = $this->createSwooleTable();

        $store = new SwooleStore($table);

        for ($i = 0; $i < 2000; ++$i) {
            $store->put(sha1("key:{$i}"), $i, 100);
        }

        $this->assertNull($store->get(sha1('key:0')));
        $this->assertSame(1999, $store->get(sha1('key:1999')));
        $this->assertLessThanOrEqual(1024, $table->count());
    }

    private function createSwooleTable()
    {
        return (new SwooleTableManager(m::mock(ContainerInterface::class)))->createTable(1024, 10240, 0.2);
    }
}
