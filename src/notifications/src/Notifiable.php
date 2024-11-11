<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Notifications;

trait Notifiable
{
    use HasDatabaseNotifications;
    use RoutesNotifications;
}
