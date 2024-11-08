<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Translation\Contracts;

interface HasLocalePreference
{
    /**
     * Get the preferred locale of the entity.
     */
    public function preferredLocale(): ?string;
}
