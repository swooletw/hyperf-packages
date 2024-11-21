<?php

declare(strict_types=1);

namespace Illuminate\Tests\Integration\Broadcasting;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class SendingBroadcastsViaAnonymousEventTest extends TestCase
{
    // TODO: waiting for queue implementation, then copy test file from laravel
    public function testEventsCanBeBroadcast()
    {
        $this->assertTrue(true);
    }
}
