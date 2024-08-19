<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\JWT\Stub;

use SwooleTW\Hyperf\JWT\Validations\AbstractValidation;

class ValidationStub extends AbstractValidation
{
    public function validate(array $payload): void
    {
    }
}
