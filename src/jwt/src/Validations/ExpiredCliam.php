<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT\Validations;

use Carbon\Carbon;
use SwooleTW\Hyperf\JWT\Exceptions\TokenExpiredException;

class ExpiredCliam extends AbstractValidation
{
    public function validate(array $payload): void
    {
        if (! $exp = ($payload['exp'] ?? null)) {
            return;
        }

        if (Carbon::now() > $this->timestamp($exp)->addSecond($payload['leeway'] ?? 0)) {
            throw new TokenExpiredException('Token has expired');
        }
    }
}
