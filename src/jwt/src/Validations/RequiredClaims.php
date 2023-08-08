<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT\Validations;

use SwooleTW\Hyperf\JWT\Exceptions\TokenInvalidException;
use SwooleTW\Hyperf\JWT\Validations\AbstractValidation;

class RequiredClaims extends AbstractValidation
{
    /**
     * @param  array  $payload
     * @return void
     */
    public function validate(array $payload): void
    {
        if (! $required = $this->config['required_claims'] ?? []) {
            return;
        }

        if (! $missingKeys = array_diff($required, array_keys($payload))) {
            return;
        }

        throw new TokenInvalidException('Claims are missing: ' . json_encode($missingKeys));
    }
}
