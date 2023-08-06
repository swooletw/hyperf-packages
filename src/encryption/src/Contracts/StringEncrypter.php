<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Encryption\Contracts;

interface StringEncrypter
{
    /**
     * Encrypt a string without serialization.
     *
     * @param  string  $value
     * @return string
     *
     * @throws \SwooleTW\Hyperf\Encryption\Exceptions\EncryptException
     */
    public function encryptString(string $value): string;

    /**
     * Decrypt the given string without unserialization.
     *
     * @param  string  $payload
     * @return string
     *
     * @throws \SwooleTW\Hyperf\Encryption\Exceptions\DecryptException
     */
    public function decryptString(string $payload): string;
}