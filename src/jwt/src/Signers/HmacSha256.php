<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT\Signers;

use Lcobucci\JWT\Signer\Hmac;

final class HmacSha256 extends Hmac
{
    public function algorithmId(): string
    {
        return 'HS256';
    }

    public function algorithm(): string
    {
        return 'sha256';
    }

    public function minimumBitsLengthForKey(): int
    {
        return 128;
    }
}
