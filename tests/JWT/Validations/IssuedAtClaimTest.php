<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\JWT\Validations;

use Carbon\Carbon;
use SwooleTW\Hyperf\JWT\Exceptions\TokenInvalidException;
use SwooleTW\Hyperf\JWT\Validations\IssuedAtClaim;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class IssuedAtClaimTest extends TestCase
{
    public function testValid()
    {
        Carbon::setTestNow('2000-01-01T00:00:00.000000Z');

        $this->expectNotToPerformAssertions();

        $validation = new IssuedAtClaim(['leeway' => 3600]);

        $validation->validate([]);
        $validation->validate(['iat' => Carbon::now()->timestamp - 3600]);
        $validation->validate(['iat' => Carbon::now()->timestamp + 3600]);
    }

    public function testInvalid()
    {
        Carbon::setTestNow('2000-01-01T00:00:00.000000Z');

        $this->expectException(TokenInvalidException::class);
        $this->expectExceptionMessage('Issued At (iat) timestamp cannot be in the future');

        $validation = new IssuedAtClaim();

        $validation->validate(['iat' => Carbon::now()->timestamp + 3600]);
    }
}
