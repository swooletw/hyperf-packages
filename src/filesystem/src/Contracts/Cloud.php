<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\FileSystem\Contracts;

interface Cloud extends Filesystem
{
    /**
     * Get the URL for the file at the given path.
     *
     * @param  string  $path
     * @return string
     */
    public function url($path);
}
