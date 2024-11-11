<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Notifications;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface as EventDispatcher;
use SwooleTW\Hyperf\Notifications\AnonymousNotifiable;
use SwooleTW\Hyperf\Notifications\ChannelManager;
use SwooleTW\Hyperf\Notifications\Notifiable;
use SwooleTW\Hyperf\Notifications\Notification;
use SwooleTW\Hyperf\Notifications\NotificationSender;

/**
 * @internal
 * @coversNothing
 */
class NotificationSenderTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testItCanSendNotificationsWithAStringVia()
    {
        $notifiable = m::mock(Notifiable::class);
        $manager = m::mock(ChannelManager::class);
        $manager->shouldReceive('driver')
            ->once()
            ->andReturnSelf();
        $manager->shouldReceive('send')
            ->once();
        $events = m::mock(EventDispatcher::class);
        $events->shouldReceive('dispatch');

        $sender = new NotificationSender($manager, $events);

        $sender->send($notifiable, new DummyNotificationWithStringVia());
    }

    public function testItCanSendNotificationsWithAnEmptyStringVia()
    {
        $notifiable = new AnonymousNotifiable();
        $manager = m::mock(ChannelManager::class);
        $events = m::mock(EventDispatcher::class);
        $events->shouldNotReceive('dispatch');

        $sender = new NotificationSender($manager, $events);

        $sender->sendNow($notifiable, new DummyNotificationWithEmptyStringVia());
    }

    public function testItCannotSendNotificationsViaDatabaseForAnonymousNotifiables()
    {
        $notifiable = new AnonymousNotifiable();
        $manager = m::mock(ChannelManager::class);
        $events = m::mock(EventDispatcher::class);
        $events->shouldNotReceive('dispatch');

        $sender = new NotificationSender($manager, $events);

        $sender->sendNow($notifiable, new DummyNotificationWithDatabaseVia());
    }
}

class DummyNotificationWithStringVia extends Notification
{
    /**
     * Get the notification channels.
     *
     * @param mixed $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return 'mail';
    }
}

class DummyNotificationWithEmptyStringVia extends Notification
{
    /**
     * Get the notification channels.
     *
     * @param mixed $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return '';
    }
}

class DummyNotificationWithDatabaseVia extends Notification
{
    /**
     * Get the notification channels.
     *
     * @param mixed $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return 'database';
    }
}

class DummyNotificationWithViaConnections extends Notification
{
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function viaConnections()
    {
        return [
            'database' => 'sync',
        ];
    }
}
