<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT\Contracts;

interface ValidationContract
{
    /**
     * @param  array  $payload
     * @return void
     */
    public function validate(array $payload): void;
}
