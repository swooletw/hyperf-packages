<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\View\Render;
use Hyperf\View\RenderInterface;

/**
 * @method static string render(string $template, array $data = [], array $config = [])
 * @method static void addNamespace(string $namespace, string|array $hints)
 * @method static void share(string $key, $value = null)
 * @method static void composer(string|array $views, \Closure|string $callback)
 * @method static void creator(string|array $views, \Closure|string $callback)
 * @method static void addExtension(string $extension, string $engine, \Closure $resolver = null)
 * @method static \Hyperf\View\Engine\EngineInterface getEngineFromPath(string $path)
 * @method static string[] getFinder()
 * @method static string[] getExtensions()
 * @method static array getShared()
 * @method static void flushState()
 * @method static void flushStateIfDoneRendering()
 * @method static void incrementRender()
 * @method static void decrementRender()
 * @method static bool doneRendering()
 * @method static void addLoop(array|object $data)
 * @method static void addLocation(string $location)
 * @method static void prependLocation(string $location)
 * @method static bool hasSection(string $name)
 * @method static string|null yieldContent(string $section, string $default = '')
 * @method static string yieldSection()
 * @method static string|null section(string $name, string|null $content = null)
 * @method static void startSection(string $section, string|null $content = null)
 * @method static void stopSection(bool $overwrite = false)
 * @method static void appendSection()
 * @method static string|null getSection(string $name, string $default = null)
 * @method static array getSections()
 * @method static void flushSections()
 * @method static void flushSectionsIfDoneRendering()
 * @method static void inject(string $section, string $content)
 * @method static void startComponent(string $name, array $data = [])
 * @method static void endComponent()
 * @method static void startComponentFirst(array $names, array $data = [])
 * @method static void startComponentsFirst(array $names, array $data = [])
 * @method static string renderComponent()
 * @method static void slot(string $name, string|null $content = null, array $attributes = [])
 * @method static void endSlot()
 *
 * @see Render
 */
class View extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RenderInterface::class;
    }
}
