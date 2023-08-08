<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Support\Providers;

use Psr\EventDispatcher\ListenerProviderInterface;
use SwooleTW\Hyperf\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected array $listen = [
        //
    ];

    public function register(): void
    {
        $provider = $this->app->get(ListenerProviderInterface::class);
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $instance = $this->app->get($listener);
                $provider->on($event, [$instance, 'handle']);
            }
        }
    }
}
