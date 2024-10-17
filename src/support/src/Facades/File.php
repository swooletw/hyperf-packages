<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Facades;

use SwooleTW\Hyperf\Filesystem\Filesystem;

/**
 * @method static bool exists(string $path)
 * @method static string get(string $path, bool $lock = false)
 * @method static string sharedGet(string $path)
 * @method static void getRequire(string $path)
 * @method static void requireOnce(string $file)
 * @method static string hash(string $path)
 * @method static void clearStatCache(string $path)
 * @method static bool|int put(string $path, $contents, bool $lock = false)
 * @method static void replace(string $path, string $content)
 * @method static int prepend(string $path, string $data)
 * @method static int append(string $path, string $data)
 * @method static void chmod(string $path, ?int $mode = null)
 * @method static bool delete(array|string $paths)
 * @method static bool move(string $path, string $target)
 * @method static bool copy(string $path, string $target)
 * @method static bool link(string $target, string $link): bool
 * @method static string name(string $path)
 * @method static string basename(string $path)
 * @method static string dirname(string $path)
 * @method static string extension(string $path)
 * @method static string type(string $path)
 * @method static false|string mimeType(string $path)
 * @method static int size(string $path)
 * @method static int lastModified(string $path)
 * @method static bool isDirectory(string $directory)
 * @method static bool isReadable(string $path)
 * @method static bool isWritable(string $path)
 * @method static bool isFile(string $file)
 * @method static array glob(string $pattern, int $flags = 0)
 * @method static array files(string $directory, bool $hidden = false)
 * @method static array allFiles(string $directory, bool $hidden = false)
 * @method static array directories(string $directory)
 * @method static bool makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false)
 * @method static bool moveDirectory(string $from, string $to, bool $overwrite = false)
 * @method static bool copyDirectory(string $directory, string $destination, ?int $options = null)
 * @method static bool deleteDirectory(string $directory, bool $preserve = false)
 * @method static bool deleteDirectories(string $directory)
 * @method static bool cleanDirectory(string $directory)
 * @method static bool windowsOs()
 *
 * @see \SwooleTW\Hyperf\Filesystem\Filesystem
 */
class File extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Filesystem::class;
    }
}
