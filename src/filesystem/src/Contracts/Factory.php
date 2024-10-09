<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\FileSystem\Contracts;

interface Factory
{
    /**
     * Get a filesystem implementation.
     *
     * @param  string|null  $name
     * @return \Hyperf\Support\Filesystem\Filesystem
     */
    public function disk($name = null);
}
