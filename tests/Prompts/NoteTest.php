<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Prompts;

use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Prompts\Prompt;

use function SwooleTW\Hyperf\Prompts\note;

/**
 * @backupStaticProperties enabled
 * @internal
 * @coversNothing
 */
class NoteTest extends TestCase
{
    public function testRendersNote()
    {
        Prompt::fake();

        note('Hello, World!');

        Prompt::assertOutputContains('Hello, World!');
    }
}
