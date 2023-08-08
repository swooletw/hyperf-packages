<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT\Contracts;

interface StorageContract
{
    /**
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $minutes
     * @return void
     */
    public function add(string $key, mixed $value, int $minutes): void;

    /**
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function forever(string $key, mixed $value): void;

    /**
     * @param  string  $key
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * @param  string  $key
     * @return bool
     */
    public function destroy(string $key): bool;

    /**
     * @return void
     */
    public function flush(): void;
}