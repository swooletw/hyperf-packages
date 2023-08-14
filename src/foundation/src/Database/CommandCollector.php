<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Database;

use Hyperf\Database\Commands\Migrations\FreshCommand;
use Hyperf\Database\Commands\Migrations\InstallCommand;
use Hyperf\Database\Commands\Migrations\MigrateCommand;
use Hyperf\Database\Commands\Migrations\RefreshCommand;
use Hyperf\Database\Commands\Migrations\ResetCommand;
use Hyperf\Database\Commands\Migrations\RollbackCommand;
use Hyperf\Database\Commands\Migrations\StatusCommand;
use Hyperf\Database\Commands\Seeders\SeedCommand;

class CommandCollector
{
    public static function getAllCommands(): array
    {
        return [
            ModelCommand::class,
            InstallCommand::class,
            MigrateCommand::class,
            FreshCommand::class,
            RefreshCommand::class,
            ResetCommand::class,
            RollbackCommand::class,
            StatusCommand::class,
            SeedCommand::class,
        ];
    }
}
