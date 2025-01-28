<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Session;

use Mockery as m;
use SessionHandlerInterface;
use SwooleTW\Hyperf\Encryption\Contracts\Encrypter;
use SwooleTW\Hyperf\Session\EncryptedStore;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class EncryptedSessionStoreTest extends TestCase
{
    public function testSessionIsProperlyEncrypted()
    {
        $session = $this->getSession();
        $session->getEncrypter()->shouldReceive('decrypt')->once()->with(serialize([]))->andReturn(serialize([]));
        $session->getHandler()->shouldReceive('read')->once()->andReturn(serialize([]));
        $session->start();
        $session->put('foo', 'bar');
        $session->flash('baz', 'boom');
        $session->now('qux', 'norf');
        $serialized = serialize([
            '_token' => $session->token(),
            'foo' => 'bar',
            'baz' => 'boom',
            '_flash' => [
                'new' => [],
                'old' => ['baz'],
            ],
        ]);
        $session->getEncrypter()->shouldReceive('encrypt')->once()->with($serialized)->andReturn($serialized);
        $session->getHandler()->shouldReceive('write')->once()->with(
            $this->getSessionId(),
            $serialized
        );
        $session->save();

        $this->assertFalse($session->isStarted());
    }

    public function getSession(): EncryptedStore
    {
        return (new EncryptedStore(
            $this->getSessionName(),
            m::mock(SessionHandlerInterface::class),
            m::mock(Encrypter::class)
        ))->setId($this->getSessionId());
    }

    protected function getSessionId(): string
    {
        return 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
    }

    protected function getSessionName(): string
    {
        return 'name';
    }
}
