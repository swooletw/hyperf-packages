<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Event\Hyperf;

use SwooleTW\Hyperf\Event\ListenerProvider;
use SwooleTW\Hyperf\Tests\Event\Hyperf\Event\Alpha;
use SwooleTW\Hyperf\Tests\Event\Hyperf\Event\Beta;
use SwooleTW\Hyperf\Tests\Event\Hyperf\Listener\AlphaListener;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ListenerProviderTest extends TestCase
{
    public function testListenNotExistEvent()
    {
        $provider = new ListenerProvider();
        $provider->on(Alpha::class, [new AlphaListener(), 'process']);
        $provider->on('NotExistEvent', [new AlphaListener(), 'process']);

        $it = $provider->getListenersForEvent(new Alpha());
        [$class, $method] = $it->current();
        $this->assertInstanceOf(AlphaListener::class, $class);
        $this->assertSame('process', $method);
        $this->assertNull($it->next());

        $it = $provider->getListenersForEvent(new Beta());
        $this->assertNull($it->current());
    }
}
