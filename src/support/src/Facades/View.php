<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\Contract\Arrayable;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use Hyperf\ViewEngine\Contract\ViewInterface;

/**
 * @method static ViewInterface file(string $path, $data = [], array $mergeData = [])
 * @method static ViewInterface make(string $view, $data = [], array $mergeData = [])
 * @method static ViewInterface first(array $views, array|Arrayable $data = [], array $mergeData = [])
 * @method static string renderWhen(bool $condition, string $view, array|Arrayable $data = [], array $mergeData = [])
 * @method static string renderUnless(bool $condition, string $view, array|Arrayable $data = [], array $mergeData = [])
 * @method static string renderEach(string $view, array $data, string $iterator, string $empty = 'raw|')
 * @method static bool exists(string $view)
 * @method static mixed share(array|string $key, $value = null)
 * @method static void incrementRender()
 * @method static void decrementRender()
 * @method static bool doneRendering()
 * @method static bool hasRenderedOnce(string $id)
 * @method static void markAsRenderedOnce(string $id)
 * @method static void addLocation(string $location)
 * @method static \Hyperf\ViewEngine\Factory addNamespace(string $namespace, array|string $hints)
 * @method static \Hyperf\ViewEngine\Factory prependNamespace(string $namespace, array|string $hints)
 * @method static \Hyperf\ViewEngine\Factory replaceNamespace(string $namespace, array|string $hints)
 * @method static void addExtension(string $extension, string $engine, ?\Closure $resolver = null)
 * @method static void flushState()
 * @method static void flushStateIfDoneRendering()
 * @method static array getExtensions()
 * @method static \Hyperf\ViewEngine\Contract\EngineResolverInterface getEngineResolver()
 * @method static \Hyperf\ViewEngine\Contract\FinderInterface getFinder()
 * @method static void setFinder(\Hyperf\ViewEngine\Contract\FinderInterface $finder)
 * @method static void flushFinderCache()
 * @method static \Psr\EventDispatcher\EventDispatcherInterface getDispatcher()
 * @method static void setDispatcher(\Psr\EventDispatcher\EventDispatcherInterface $events)
 * @method static \Psr\Container\ContainerInterface getContainer()
 * @method static void setContainer(\Psr\Container\ContainerInterface $container)
 * @method static mixed shared(string $key, $default = null)
 * @method static array getShared()
 * @method static void startComponent($name, array $data = [])
 * @method static void endComponent()
 * @method static string renderComponent()
 * @method static void slot($name, $content = null, array $attributes = [])
 * @method static void endSlot()
 * @method static bool hasSection(string $name)
 * @method static string yieldContent(string $section, string $default = '')
 * @method static string yieldSection()
 * @method static string section(string $name, string|null $content = null)
 * @method static void append(string $section, string $content)
 * @method static void flushSections()
 * @method static void addLoop($data)
 * @method static void incrementLoopIndices()
 * @method static void popLoop()
 * @method static \stdClass|null getLastLoop()
 * @method static array getLoopStack()
 * @method static void startPush(string $section, string $content = '')
 * @method static string stopPush()
 * @method static void startPrepend(string $section, string $content = '')
 * @method static string stopPrepend()
 * @method static string yieldPushContent(string $section, string $default = '')
 * @method static void flushStacks()
 * @method static void startTranslation(array $replacements = [])
 * @method static string renderTranslation()
 * @method static void composer($views, \Closure|string $callback)
 * @method static void callComposer(\Hyperf\ViewEngine\Contract\ViewInterface $view)
 * @method static void creator($views, \Closure|string $callback)
 * @method static void callCreator(\Hyperf\ViewEngine\Contract\ViewInterface $view)
 * @method static array getComposers()
 *
 * @see \Hyperf\ViewEngine\Factory
 */
class View extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FactoryInterface::class;
    }
}
