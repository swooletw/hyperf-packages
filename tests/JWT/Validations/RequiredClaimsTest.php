<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\JWT\Validations;

use SwooleTW\Hyperf\JWT\Exceptions\TokenInvalidException;
use SwooleTW\Hyperf\JWT\Validations\RequiredClaims;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RequiredClaimsTest extends TestCase
{
    public function testValid()
    {
        $this->expectNotToPerformAssertions();

        (new RequiredClaims([]))->validate([]);
        (new RequiredClaims(['required_claims' => ['sub']]))->validate(['sub' => 'foo']);
    }

    public function testInvalid()
    {
        $this->expectException(TokenInvalidException::class);
        $this->expectExceptionMessage('Claims are missing: ["sub"]');

        (new RequiredClaims(['required_claims' => ['sub']]))->validate([]);
    }
}
