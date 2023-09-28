<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\JWT\Providers;

use SwooleTW\Hyperf\Tests\JWT\Stub\ProviderStub;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ProviderTest extends TestCase
{
    protected $provider;

    public function testSetTheAlgo()
    {
        $provider = new ProviderStub('secret', 'HS256', []);

        $provider->setAlgo('HS512');

        $this->assertSame('HS512', $provider->getAlgo());
    }

    public function testSetTheSecret()
    {
        $provider = new ProviderStub('secret', 'HS256', []);

        $provider->setSecret('foo');

        $this->assertSame('foo', $provider->getSecret());
    }
}
