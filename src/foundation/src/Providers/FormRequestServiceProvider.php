<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Providers;

use Hyperf\Validation\Request\FormRequest;
use SwooleTW\Hyperf\Support\ServiceProvider;

class FormRequestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->resolving(FormRequest::class, function (FormRequest $request) {
            $request->validateResolved();
        });
    }
}