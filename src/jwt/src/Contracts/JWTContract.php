<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT\Contracts;

interface JWTContract
{
    /**
     * @param  array  $payload
     * @return string
     */
    public function encode(array $payload): string;

    /**
     * @param  string  $token
     * @return array
     */
    public function decode(string $token): array;
}
