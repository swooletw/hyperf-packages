<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Telescope\Watchers;

use Hyperf\Contract\ConfigInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Notifications\AnonymousNotifiable;
use SwooleTW\Hyperf\Notifications\Events\NotificationSent;
use SwooleTW\Hyperf\Notifications\Notification;
use SwooleTW\Hyperf\Telescope\EntryType;
use SwooleTW\Hyperf\Telescope\Watchers\NotificationWatcher;
use SwooleTW\Hyperf\Tests\Telescope\FeatureTestCase;

/**
 * @internal
 * @coversNothing
 */
class NotificationWatcherTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->get(ConfigInterface::class)
            ->set('telescope.watchers', [
                NotificationWatcher::class => true,
            ]);

        $this->startTelescope();
    }

    public function testNotificationWatcherRegistersEntry()
    {
        $notifiable = new AnonymousNotifiable();
        $notifiable->routes = ['route1', 'route2'];
        $event = new NotificationSent(
            $notifiable,
            new Notification(),
            'channel',
            'response'
        );

        $this->app->get(EventDispatcherInterface::class)
            ->dispatch($event);

        $entry = $this->loadTelescopeEntries()->first();

        $this->assertSame(EntryType::NOTIFICATION, $entry->type);
        $this->assertSame(Notification::class, $entry->content['notification']);
        $this->assertFalse($entry->content['queued']);
        $this->assertSame($entry->content['notifiable'], 'Anonymous:route1,route2');
        $this->assertSame('channel', $entry->content['channel']);
        $this->assertSame('response', $entry->content['response']);
    }
}
