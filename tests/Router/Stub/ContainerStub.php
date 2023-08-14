<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Router\Stub;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Router;
use Hyperf\Server\Server;
use Mockery;

class ContainerStub
{
    public static function getContainer()
    {
        ! defined('BASE_PATH') && define('BASE_PATH', __DIR__);

        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')
            ->with(DispatcherFactory::class)
            ->andReturnUsing(function () {
                $dispatcher = new DispatcherFactory();
                Router::get('/', 'Index::index', ['name' => 'index']);
                Router::get('/user/{id:\d+}', 'User::info', ['name' => 'user.info']);
                Router::get('/user', 'User::index', ['name' => 'user.list']);
                Router::get('/author/{user}/book/{name}', 'AuthorBook::index', ['name' => 'author.book']);
                Router::get('/author/{user}[/role/{name}]', 'AuthorRole::index', ['name' => 'author.role']);
                Router::get('/book[/author]', 'Book::author', ['name' => 'book.author']);
                return $dispatcher;
            });

        $container->shouldReceive('get')
            ->with(ConfigInterface::class)
            ->andReturn(new Config([
                'server' => [
                    'servers' => [
                        ['name' => 'http', 'type' => Server::SERVER_HTTP],
                        ['name' => 'http2', 'type' => Server::SERVER_HTTP],
                    ],
                ],
            ]));

        return $container;
    }
}
