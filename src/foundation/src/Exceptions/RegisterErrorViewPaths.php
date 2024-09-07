<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Exceptions;

use SwooleTW\Hyperf\Support\Facades\View;

class RegisterErrorViewPaths
{
    /**
     * Register the error view paths.
     */
    public function __invoke()
    {
        View::replaceNamespace('errors', collect(config('view.config.view_path'))->map(function ($path) {
            return "{$path}/errors";
        })->push(__DIR__ . '/views')->all());
    }
}
