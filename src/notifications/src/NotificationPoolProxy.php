<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Notifications;

use SwooleTW\Hyperf\ObjectPool\PoolProxy;

class NotificationPoolProxy extends PoolProxy
{
    /**
     * Send the given notification..
     */
    public function send(mixed $notifiable, Notification $notification)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }
}
