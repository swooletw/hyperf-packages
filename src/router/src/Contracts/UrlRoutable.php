<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Router\Contracts;

interface UrlRoutable
{
    /**
     * Get the value of the model's route key.
     */
    public function getRouteKey(): string;

    // TODO: Route model binding
    // /**
    //  * Get the route key for the model.
    //  */
    // public function getRouteKeyName(): string;

    // TODO: Route model binding
    // /**
    //  * Retrieve the model for a bound value.
    //  */
    // public function resolveRouteBinding(string $value, ?string $field = null): Hyperf\Database\Model\Model;
}
