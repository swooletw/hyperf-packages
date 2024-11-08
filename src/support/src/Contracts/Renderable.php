<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Contracts;

interface Renderable
{
    /**
     * Get the evaluated contents of the object.
     */
    public function render(): string;
}
