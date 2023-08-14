<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT\Contracts;

interface ValidationContract
{
    public function validate(array $payload): void;
}
