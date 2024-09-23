<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Providers;

use Hyperf\Validation\Contract\ValidatesWhenResolved;
use SwooleTW\Hyperf\Support\ServiceProvider;

class FormRequestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->resolving(ValidatesWhenResolved::class, function (ValidatesWhenResolved $request) {
            $request->validateResolved();
        });
    }
}
