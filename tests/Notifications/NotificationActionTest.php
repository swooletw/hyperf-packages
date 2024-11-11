<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Notifications;

use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Notifications\Action;

/**
 * @internal
 * @coversNothing
 */
class NotificationActionTest extends TestCase
{
    public function testActionIsCreatedProperly()
    {
        $action = new Action('Text', 'url');

        $this->assertSame('Text', $action->text);
        $this->assertSame('url', $action->url);
    }
}
