<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Notifications;

use Hyperf\Database\Model\Model;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Notifications\Channels\DatabaseChannel;
use SwooleTW\Hyperf\Notifications\Messages\DatabaseMessage;
use SwooleTW\Hyperf\Notifications\Notification;

/**
 * @internal
 * @coversNothing
 */
class NotificationDatabaseChannelTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testDatabaseChannelCreatesDatabaseRecordWithProperData()
    {
        $notification = new NotificationDatabaseChannelTestNotification();
        $notification->id = '1';
        $notifiable = m::mock();

        $notifiable->shouldReceive('routeNotificationFor->create')->with([
            'id' => 1,
            'type' => get_class($notification),
            'data' => ['invoice_id' => '1'],
            'read_at' => null,
        ])->andReturn(m::mock(Model::class));

        $channel = new DatabaseChannel();
        $channel->send($notifiable, $notification);
    }

    public function testCorrectPayloadIsSentToDatabase()
    {
        $notification = new NotificationDatabaseChannelTestNotification();
        $notification->id = '1';
        $notifiable = m::mock();

        $notifiable->shouldReceive('routeNotificationFor->create')->with([
            'id' => 1,
            'type' => get_class($notification),
            'data' => ['invoice_id' => '1'],
            'read_at' => null,
            'something' => 'else',
        ])->andReturn(m::mock(Model::class));

        $channel = new ExtendedDatabaseChannel();
        $channel->send($notifiable, $notification);
    }

    public function testCustomizeTypeIsSentToDatabase()
    {
        $notification = new NotificationDatabaseChannelCustomizeTypeTestNotification();
        $notification->id = '1';
        $notifiable = m::mock();

        $notifiable->shouldReceive('routeNotificationFor->create')->with([
            'id' => '1',
            'type' => 'MONTHLY',
            'data' => ['invoice_id' => '1'],
            'read_at' => null,
            'something' => 'else',
        ])->andReturn(m::mock(Model::class));

        $channel = new ExtendedDatabaseChannel();
        $channel->send($notifiable, $notification);
    }
}

class NotificationDatabaseChannelTestNotification extends Notification
{
    public function toDatabase($notifiable)
    {
        return new DatabaseMessage(['invoice_id' => '1']);
    }
}

class NotificationDatabaseChannelCustomizeTypeTestNotification extends Notification
{
    public function toDatabase($notifiable)
    {
        return new DatabaseMessage(['invoice_id' => '1']);
    }

    public function databaseType()
    {
        return 'MONTHLY';
    }
}

class ExtendedDatabaseChannel extends DatabaseChannel
{
    protected function buildPayload($notifiable, Notification $notification): array
    {
        return array_merge(parent::buildPayload($notifiable, $notification), [
            'something' => 'else',
        ]);
    }
}
