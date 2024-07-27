<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Foundation\Concerns;

use SwooleTW\Hyperf\Container\DefinitionSource;
use SwooleTW\Hyperf\Foundation\Application;

trait HasMockedApplication
{
    protected function getApplication(array $definitionSources = [], string $basePath = 'base_path'): Application
    {
        return new Application(
            new DefinitionSource($definitionSources),
            $basePath
        );
    }
}
