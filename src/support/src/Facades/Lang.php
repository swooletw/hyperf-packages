<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use Hyperf\Contract\TranslatorLoaderInterface;
use Hyperf\Translation\MessageSelector;
use Hyperf\Translation\Translator;

/**
 * @method static string trans(string $key, array $replace = [], ?string $locale = null)
 * @method static string transChoice(string $key, int $number, array $replace = [], ?string $locale = null)
 * @method static string getLocale()
 * @method static void setLocale(string $locale)
 * @method static string getDefaultLocale()
 * @method static void setDefaultLocale(string $locale)
 * @method static string|array|null get(string $key, array $replace = [], ?string $locale = null, bool $fallback = true)
 * @method static bool has(string $key, ?string $locale = null, bool $fallback = true)
 * @method static string|array|null getFromJson(string $key, array $replace = [], ?string $locale = null)
 * @method static void addNamespace(string $namespace, string $hint)
 * @method static void addJsonPath(string $path)
 * @method static array parseKey(string $key)
 * @method static void setSelector(MessageSelector $selector)
 * @method static MessageSelector getSelector()
 * @method static void setLoader(TranslatorLoaderInterface $loader)
 * @method static TranslatorLoaderInterface getLoader()
 * @method static void setFallback(string $fallback)
 * @method static string getFallback()
 *
 * @see \Hyperf\Translation\Translator
 */
class Lang extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Translator::class;
    }
}
