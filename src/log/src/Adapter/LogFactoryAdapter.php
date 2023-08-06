<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Log\Adapter;

use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class LogFactoryAdapter extends LoggerFactory
{
    public function make($name = 'hyperf', $group = 'default'): LoggerInterface
    {
        return $this->container->get(LoggerInterface::class)->channel();
    }

    public function get($name = 'hyperf', $group = 'default'): LoggerInterface
    {
        return $this->container->get(LoggerInterface::class)->channel();
    }
}
