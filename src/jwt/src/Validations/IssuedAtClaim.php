<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT\Validations;

use Carbon\Carbon;
use SwooleTW\Hyperf\JWT\Exceptions\TokenInvalidException;
use SwooleTW\Hyperf\JWT\Validations\AbstractValidation;

class IssuedAtClaim extends AbstractValidation
{
    /**
     * @param  array  $payload
     * @return void
     */
    public function validate(array $payload): void
    {
        if (! $iat = ($payload['iat'] ?? null)) {
            return;
        }

        if ($this->timestamp($iat)->subSecond($this->config['leeway'] ?? 0) > Carbon::now()) {
            throw new TokenInvalidException('Issued At (iat) timestamp cannot be in the future');
        }
    }
}
