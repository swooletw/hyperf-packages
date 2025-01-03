<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Prompts;

use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Foundation\Testing\Concerns\RunTestsInCoroutine;
use SwooleTW\Hyperf\Prompts\Prompt;

use function SwooleTW\Hyperf\Prompts\spin;

/**
 * @backupStaticProperties enabled
 * @internal
 * @coversNothing
 */
class SpinnerTest extends TestCase
{
    use RunTestsInCoroutine;

    public function testSpinner()
    {
        Prompt::fake();

        $result = spin(function () {
            return 'done';
        }, 'Running...');

        $this->assertSame('done', $result);

        Prompt::assertOutputContains('Running...');
    }
}
