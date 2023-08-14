<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Cache;

use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Cache\NullStore;

/**
 * @internal
 * @coversNothing
 */
class CacheNullStoreTest extends TestCase
{
    public function testItemsCanNotBeCached()
    {
        $store = new NullStore();
        $store->put('foo', 'bar', 10);
        $this->assertNull($store->get('foo'));
    }

    public function testGetMultipleReturnsMultipleNulls()
    {
        $store = new NullStore();

        $this->assertEquals([
            'foo' => null,
            'bar' => null,
        ], $store->many([
            'foo',
            'bar',
        ]));
    }

    public function testIncrementAndDecrementReturnFalse()
    {
        $store = new NullStore();
        $this->assertFalse($store->increment('foo'));
        $this->assertFalse($store->decrement('foo'));
    }
}
