<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT\Validations;

use Carbon\Carbon;
use SwooleTW\Hyperf\JWT\Exceptions\TokenExpiredException;
use SwooleTW\Hyperf\JWT\Validations\AbstractValidation;

class ExpiredCliam extends AbstractValidation
{
    /**
     * @param  array  $payload
     * @return void
     */
    public function validate(array $payload): void
    {
        if (! $exp = ($payload['exp'] ?? null)) {
            return;
        }

        if (Carbon::now() > ($this->timestamp($exp)->addSecond($payload['leeway'] ?? 0))) {
            throw new TokenExpiredException('Token has expired');
        }
    }
}
