<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\JWT\Storage;

use Mockery;
use Mockery\MockInterface;
use SwooleTW\Hyperf\Cache\Contracts\Repository as CacheRepository;
use SwooleTW\Hyperf\JWT\Storage\TaggedCache;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TaggedCacheTest extends TestCase
{
    /**
     * @var CacheRepository|MockInterface
     */
    protected CacheRepository $cache;

    protected TaggedCache $storage;

    protected function setUp(): void
    {
        /** @var CacheRepository|MockInterface */
        $cache = Mockery::mock(CacheRepository::class);

        $this->cache = $cache;
        $this->storage = new TaggedCache($this->cache);

        $this->cache->shouldReceive('tags')->with(['jwt_blacklist'])->once()->andReturnSelf();
    }

    public function testAddTheItemToTaggedStorage()
    {
        $this->cache->shouldReceive('put')->with('foo', 'bar', 10 * 60)->once();

        $this->storage->add('foo', 'bar', 10);
    }

    public function testAddTheItemToTaggedStorageForever()
    {
        $this->cache->shouldReceive('forever')->with('foo', 'bar')->once();

        $this->storage->forever('foo', 'bar');
    }

    public function testGetAnItemFromTaggedStorage()
    {
        $this->cache->shouldReceive('get')->with('foo')->once()->andReturn(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $this->storage->get('foo'));
    }

    public function testRemoveTheItemFromTaggedStorage()
    {
        $this->cache->shouldReceive('forget')->with('foo')->once()->andReturn(true);

        $this->assertTrue($this->storage->destroy('foo'));
    }

    public function testRemoveAllTaggedItemsFromStorage()
    {
        $this->cache->shouldReceive('flush')->withNoArgs()->once();

        $this->storage->flush();
    }
}
