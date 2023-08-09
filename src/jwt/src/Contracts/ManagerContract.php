<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT\Contracts;

interface ManagerContract
{
    /**
     * @param  array  $payload
     * @return string
     */
    public function encode(array $payload): string;

    /**
     * @param  string  $token
     * @param  bool  $validate
     * @param  bool  $checkBlacklist
     * @return array
     */
    public function decode(string $token, bool $validate = true, bool $checkBlacklist = true): array;

    /**
     * @param  string  $token
     * @param  bool  $forceForever
     * @return string
     */
    public function refresh(string $token, bool $forceForever = false): string;

     /**
     * @param  string  $token
     * @param  bool  $forceForever
     * @return boolean
     */
    public function invalidate(string $token, bool $forceForever = false): bool;
}
