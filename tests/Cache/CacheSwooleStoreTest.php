<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Cache;

use Carbon\Carbon;
use Hyperf\Stringable\Str;
use Swoole\Table;
use SwooleTW\Hyperf\Cache\SwooleStore;
use SwooleTW\Hyperf\Cache\SwooleTable;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CacheSwooleStoreTest extends TestCase
{
    public function testCanRetrieveItemsFromStore(): void
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

    private function createSwooleTable()
    {
        $cacheTable = new SwooleTable(1000);

        $cacheTable->column('value', Table::TYPE_STRING, 10000);
        $cacheTable->column('expiration', Table::TYPE_INT);

        $cacheTable->create();

        return $cacheTable;
    }
}
