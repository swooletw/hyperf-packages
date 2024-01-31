<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Watcher;

use Hyperf\Watcher\Watcher as BaseWatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Watcher\Events\BeforeServerRestart;

class Watcher extends BaseWatcher
{
    public function restart($isStart = true)
    {
        if (! $isStart) {
            $this->container->get(EventDispatcherInterface::class)->dispatch(new BeforeServerRestart());
        }

        parent::restart($isStart);
    }
}
