<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Listeners;

use Closure;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Support\DotenvManager;
use Psr\Container\ContainerInterface;

class ReloadDotenvAndConfig implements ListenerInterface
{
    protected static array $modifiedItems = [];

    protected static bool $stopCallback = false;

    public function __construct(protected ContainerInterface $container)
    {
        $this->setConfigCallback();

        /** @var \SwooleTW\Hyperf\Container\Contracts\Container $container */
        $container->afterResolving(ConfigInterface::class, function (ConfigInterface $config) {
            if (static::$stopCallback) {
                return;
            }

            static::$stopCallback = true;
            foreach (static::$modifiedItems as $key => $value) {
                $config->set($key, $value);
            }
            static::$stopCallback = false;
        });
    }

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
        ];
    }

    public function process(object $event): void
    {
        $this->reloadDotenv();
        $this->reloadConfig();
    }

    protected function reloadConfig(): void
    {
        /* @phpstan-ignore-next-line */
        $this->container->unbind(ConfigInterface::class);
    }

    protected function reloadDotenv(): void
    {
        if (file_exists(BASE_PATH . '/.env')) {
            DotenvManager::reload([BASE_PATH]);
        }
    }

    protected function setConfigCallback(): void
    {
        $this->container->get(ConfigInterface::class)
            ->afterSettingCallback(function (array $values) {
                static::$modifiedItems = array_merge(
                    static::$modifiedItems,
                    $values
                );
            });
    }
}
