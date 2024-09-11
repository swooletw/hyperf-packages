<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Database\Migrations;

use Hyperf\Database\Migrations\MigrationCreator as HyperfMigrationCreator;

class MigrationCreator extends HyperfMigrationCreator
{
    /**
     * Get the path to the stubs.
     */
    public function stubPath(): string
    {
        return __DIR__ . '/stubs';
    }
}
