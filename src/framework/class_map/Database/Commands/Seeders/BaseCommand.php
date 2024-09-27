<?php

declare(strict_types=1);

namespace Hyperf\Database\Commands\Seeders;

use Hyperf\Command\Command;

abstract class BaseCommand extends Command
{
    /**
     * Get seeder path (either specified by '--path' option or default location).
     */
    protected function getSeederPaths(): string
    {
        if (! is_null($targetPath = $this->input->getOption('path'))) {
            return ! $this->usingRealPath()
                ? BASE_PATH . '/' . $targetPath
                : $targetPath;
        }

        return BASE_PATH . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders';
    }

    /**
     * Determine if the given path(s) are pre-resolved "real" paths.
     */
    protected function usingRealPath(): bool
    {
        return $this->input->hasOption('realpath') && $this->input->getOption('realpath');
    }
}
