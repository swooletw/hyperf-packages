<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Http\Listeners;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\HttpServer\Request;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Http\RequestMacro;

class MixinRequestMacros implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
        ];
    }

    public function process(object $event): void
    {
        Request::mixin(new RequestMacro());
    }
}
