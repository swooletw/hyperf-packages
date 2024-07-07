<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Foundation\Di;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Foundation\Di\DotenvManager;

use function Hyperf\Support\env;

/**
 * @internal
 * @coversNothing
 */
class DotenvManagerTest extends TestCase
{
    public function testLoad()
    {
        DotenvManager::load([__DIR__ . '/envs/oldEnv']);

        $this->assertEquals('1.0', env('TEST_VERSION'));
        $this->assertTrue(env('OLD_FLAG'));
    }

    public function testReload()
    {
        DotenvManager::load([__DIR__ . '/envs/oldEnv']);
        DotenvManager::reload([__DIR__ . '/envs/newEnv'], true);

        $this->assertEquals('2.0', env('TEST_VERSION'));
        $this->assertNull(env('OLD_FLAG'));
        $this->assertTrue(env('NEW_FLAG'));
    }
}
