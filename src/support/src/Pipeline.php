<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support;

use Hyperf\Conditionable\Conditionable;
use Hyperf\Pipeline\Pipeline as BasePipeline;
use SwooleTW\Hyperf\Foundation\ApplicationContext;

class Pipeline extends BasePipeline
{
    use Conditionable;

    public function make(): static
    {
        return new static(
            ApplicationContext::getContainer()
        );
    }
}
