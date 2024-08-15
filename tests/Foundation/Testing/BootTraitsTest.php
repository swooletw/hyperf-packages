<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Foundation\Testing;

use ReflectionMethod;
use SwooleTW\Hyperf\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class BootTraitsTest extends TestCase
{
    use TestTrait;

    public function testSetUpTraits()
    {
        $testCase = new TestCaseWithTrait('foo');

        $method = new ReflectionMethod($testCase, 'setUpTraits');
        $method->invoke($testCase);

        $this->assertTrue($testCase->setUp);

        $method = new ReflectionMethod($testCase, 'callBeforeApplicationDestroyedCallbacks');
        $method->invoke($testCase);

        $this->assertTrue($testCase->tearDown);
    }
}

/**
 * @internal
 * @coversNothing
 */
class TestCaseWithTrait extends ApplicationTestCase
{
    use TestTrait;
}

trait TestTrait
{
    public bool $setUp = false;

    public bool $tearDown = false;

    public function setUpTestTrait()
    {
        $this->setUp = true;
    }

    public function tearDownTestTrait()
    {
        $this->tearDown = true;
    }
}