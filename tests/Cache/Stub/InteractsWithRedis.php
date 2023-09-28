<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Cache\Stub;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Pool\Channel;
use Hyperf\Pool\LowFrequencyInterface;
use Hyperf\Pool\PoolOption;
use Hyperf\Redis\Frequency;
use Hyperf\Redis\Pool\PoolFactory;
use Hyperf\Redis\Pool\RedisPool;
use Hyperf\Redis\Redis;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Mockery;

trait InteractsWithRedis
{
    /**
     * Redis manager instance.
     *
     * @var Redis
     */
    private $redis;

    /**
     * @var RedisFactory
     */
    private $factory;

    /**
     * Setup redis connection.
     */
    public function setUpRedis()
    {
        $this->factory = $this->getRedisFactory();
        $this->redis = $this->factory->get('default');
        $this->redis->flushDB();
    }

    /**
     * Teardown redis connection.
     */
    public function tearDownRedis()
    {
        $this->redis->flushDB();
    }

    /**
     * Run test if redis is available.
     *
     * @param callable $callback
     */
    public function ifRedisAvailable($callback)
    {
        $this->setUpRedis();

        $callback();

        $this->tearDownRedis();
    }

    private function getRedisFactory($optinos = [])
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config = new Config([
            'redis' => [
                'default' => [
                    'host' => '127.0.0.1',
                    'auth' => null,
                    'port' => 6379,
                    'db' => 0,
                    'options' => $optinos,
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => 30,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => 60,
                    ],
                ],
            ],
        ]));
        $frequency = Mockery::mock(LowFrequencyInterface::class);
        $frequency->shouldReceive('isLowFrequency')->andReturn(false);
        $container->shouldReceive('make')->with(Frequency::class, Mockery::any())->andReturn($frequency);
        $container->shouldReceive('make')->with(RedisPool::class, ['name' => 'default'])->andReturnUsing(function () use ($container) {
            return new RedisPool($container, 'default');
        });
        $container->shouldReceive('make')->with(Channel::class, ['size' => 30])->andReturn(new Channel(30));
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new PoolOption(...array_values($args));
        });
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('warning')->withAnyArgs()->andReturnUsing(function ($args) {
                var_dump($args);
            });
            return $logger;
        });
        ApplicationContext::setContainer($container);

        $factory = new PoolFactory($container);
        $container->shouldReceive('make')->with(RedisProxy::class, Mockery::any())->andReturnUsing(function ($_, $args) use ($factory) {
            return new RedisProxy($factory, $args['pool']);
        });

        return new RedisFactory($config);
    }
}
