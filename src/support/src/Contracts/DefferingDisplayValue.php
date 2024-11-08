<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Contracts;

interface DeferringDisplayableValue
{
    /**
     * Resolve the displayable value that the class is deferring.
     */
    public function resolveDisplayableValue(): Htmlable|string;
}
