<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Notifications;

use Closure;
use Hyperf\Context\Context;
use Hyperf\Stringable\Str;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Bus\Contracts\Dispatcher as BusDispatcherContract;
use SwooleTW\Hyperf\Notifications\Channels\DatabaseChannel;
use SwooleTW\Hyperf\Notifications\Channels\MailChannel;
use SwooleTW\Hyperf\Notifications\Channels\SlackNotificationRouterChannel;
use SwooleTW\Hyperf\Notifications\Contracts\Dispatcher as DispatcherContract;
use SwooleTW\Hyperf\Notifications\Contracts\Factory as FactoryContract;
use SwooleTW\Hyperf\ObjectPool\Traits\HasPoolProxy;
use SwooleTW\Hyperf\Support\Manager;

class ChannelManager extends Manager implements DispatcherContract, FactoryContract
{
    use HasPoolProxy;

    /**
     * The default channel used to deliver messages.
     */
    protected string $defaultChannel = 'mail';

    /**
     * The locale used when sending notifications.
     */
    protected ?string $locale = null;

    /**
     * The pool proxy class.
     */
    protected string $poolProxyClass = NotificationPoolProxy::class;

    /**
     * The array of drivers which will be wrapped as pool proxies.
     */
    protected array $poolables = ['slack'];

    /**
     * The array of pool config for drivers.
     */
    protected array $poolConfig = [];

    /**
     * Send the given notification to the given notifiable entities.
     */
    public function send(mixed $notifiables, mixed $notification): void
    {
        (new NotificationSender(
            $this,
            $this->container->get(BusDispatcherContract::class),
            $this->container->get(EventDispatcherInterface::class),
            $this->getLocale()
        ))->send($notifiables, $notification);
    }

    /**
     * Send the given notification immediately.
     */
    public function sendNow(mixed $notifiables, mixed $notification, ?array $channels = null): void
    {
        (new NotificationSender(
            $this,
            $this->container->get(BusDispatcherContract::class),
            $this->container->get(EventDispatcherInterface::class),
            $this->getLocale()
        ))->sendNow($notifiables, $notification, $channels);
    }

    /**
     * Get a channel instance.
     */
    public function channel(?string $name = null): mixed
    {
        return $this->driver($name);
    }

    /**
     * Create an instance of the database driver.
     */
    protected function createDatabaseDriver(): DatabaseChannel
    {
        return $this->container->get(DatabaseChannel::class);
    }

    /**
     * Create an instance of the mail driver.
     */
    protected function createMailDriver(): MailChannel
    {
        return $this->container->get(MailChannel::class);
    }

    /**
     * Create an instance of the slack driver.
     */
    protected function createSlackDriver(): SlackNotificationRouterChannel
    {
        return $this->container->get(SlackNotificationRouterChannel::class);
    }

    /**
     * Create a new driver instance.
     *
     * @throws InvalidArgumentException
     */
    protected function createDriver(string $driver): mixed
    {
        $poolConfig = $this->getPoolConfig($driver);
        $hasPool = in_array($driver, $this->poolables);
        if (isset($this->customCreators[$driver])) {
            if ($hasPool) {
                return $this->createPoolProxy(
                    $driver,
                    fn () => $this->callCustomCreator($driver),
                    $poolConfig
                );
            }
            return $this->callCustomCreator($driver);
        }

        $method = 'create' . Str::studly($driver) . 'Driver';

        if (! method_exists($this, $method)) {
            if (class_exists($driver)) {
                return $this->container->get($driver);
            }

            throw new InvalidArgumentException("Driver [{$driver}] is not supported.");
        }

        if ($hasPool) {
            return $this->createPoolProxy(
                $driver,
                fn () => $this->{$method}(),
                $poolConfig
            );
        }

        return $this->{$method}();
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @return $this
     */
    public function extend(string $driver, Closure $callback, bool $poolable = false): static
    {
        if ($poolable) {
            $this->addPoolable($driver);
        }

        return parent::extend($driver, $callback);
    }

    /**
     * Register pool config for custom driver.
     *
     * @return $this
     */
    public function setPoolConfig(string $driver, array $config): static
    {
        $this->poolConfig[$driver] = $config;

        return $this;
    }

    /**
     * Get pool config for custom driver.
     */
    public function getPoolConfig(string $driver): array
    {
        return $this->poolConfig[$driver] ?? [];
    }

    /**
     * Get the default channel driver name.
     */
    public function getDefaultDriver(): string
    {
        return Context::get('__notifications.defaultChannel', $this->defaultChannel);
    }

    /**
     * Get the default channel driver name.
     */
    public function deliversVia(): string
    {
        return $this->getDefaultDriver();
    }

    /**
     * Set the default channel driver name.
     */
    public function deliverVia(string $channel): void
    {
        Context::set('__notifications.defaultChannel', $channel);
    }

    /**
     * Set the locale of notifications.
     */
    public function locale(string $locale): static
    {
        Context::set('__notifications.defaultLocale', $locale);

        return $this;
    }

    /**
     * Get the locale of notifications.
     */
    public function getLocale(): ?string
    {
        return Context::get('__notifications.defaultLocale', $this->locale);
    }
}
