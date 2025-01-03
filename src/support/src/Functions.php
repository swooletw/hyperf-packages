<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support;

use BackedEnum;
use Symfony\Component\Process\PhpExecutableFinder;
use UnitEnum;

/**
 * Return a scalar value for the given value that might be an enum.
 *
 * @internal
 *
 * @template TValue
 * @template TDefault
 *
 * @param TValue $value
 * @param callable(TValue): TDefault|TDefault $default
 * @return ($value is empty ? TDefault : mixed)
 */
function enum_value($value, $default = null)
{
    return transform($value, fn ($value) => match (true) {
        $value instanceof BackedEnum => $value->value,
        $value instanceof UnitEnum => $value->name,

        default => $value,
    }, $default ?? $value);
}

/**
 * Determine the PHP Binary.
 */
function php_binary(): string
{
    return (new PhpExecutableFinder())->find(false) ?: 'php';
}
