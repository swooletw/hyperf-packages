<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache;

use Hyperf\Contract\ConfigInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Swoole\Table;

class SwooleTableManager
{
    protected array $tables = [];

    public function __construct(
        protected ContainerInterface $app
    ) {}

    public function get(string $name): Table
    {
        return $this->tables[$name] ?? $this->resolve($name);
    }

    protected function resolve(string $name): Table
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Swoole table [{$name}] is not defined.");
        }

        $table = new SwooleTable($config['rows']);

        $table->column('value', Table::TYPE_STRING, $config['bytes']);
        $table->column('expiration', Table::TYPE_INT);

        $table->create();

        return $table;
    }

    protected function getConfig(string $name): ?array
    {
        if (! is_null($name) && $name !== 'null') {
            return $this->app->get(ConfigInterface::class)->get("swoole_table.{$name}");
        }

        return null;
    }
}
