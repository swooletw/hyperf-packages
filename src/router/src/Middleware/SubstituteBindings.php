<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router\Middleware;

use Closure;
use Hyperf\Database\Model\Model;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\Di\ReflectionType;
use Hyperf\HttpServer\Router\Dispatched;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Hyperf\Support\make;

class SubstituteBindings implements MiddlewareInterface
{
    public function __construct(protected ContainerInterface $container) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);

        if (! $dispatched->isFound()) {
            return $handler->handle($request);
        }

        $definitions = $this->getDefinitions($dispatched->handler->callback);
        $params = $dispatched->params;

        $dispatched->params = $this->substituteBindings($definitions, $params);

        return $handler->handle($request);
    }

    /**
     * @return ReflectionType[]
     */
    protected function getDefinitions(Closure|string|array $callback): array
    {
        if ($callback instanceof Closure) {
            return $this->getClosureDefinitions($callback);
        }

        if (is_string($callback)) {
            $callback = explode('@', $callback);
        }

        return $this->getMethodDefinitions($callback);
    }

    /**
     * @return ReflectionType[]
     */
    protected function getClosureDefinitions(Closure $callback): array
    {
        if (! $this->container->has(ClosureDefinitionCollectorInterface::class)) {
            return [];
        }

        return $this->container->get(ClosureDefinitionCollectorInterface::class)->getParameters($callback);
    }

    /**
     * @return ReflectionType[]
     */
    protected function getMethodDefinitions(array $callback): array
    {
        $controller = $callback[0];
        $action = $callback[1];

        return $this->container->get(MethodDefinitionCollectorInterface::class)->getParameters($controller, $action);
    }

    /**
     * @param ReflectionType[] $definitions
     */
    protected function substituteBindings(array $definitions, array $params): array
    {
        foreach ($definitions as $definition) {
            $name = $definition->getMeta('name');
            $class = $definition->getName();

            if (is_a($class, Model::class, true) && array_key_exists($name, $params)) {
                $routeKey = $params[$name];
                $params[$name] = $class::where(make($class)->getRouteKeyName(), $routeKey)->firstOrFail();
            }
        }

        return $params;
    }
}
