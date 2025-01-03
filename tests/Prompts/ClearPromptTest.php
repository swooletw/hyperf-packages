<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Prompts;

use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Prompts\Prompt;

use function SwooleTW\Hyperf\Prompts\clear;

/**
 * @backupStaticProperties enabled
 * @internal
 * @coversNothing
 */
class ClearPromptTest extends TestCase
{
    public function testPromptClear()
    {
        Prompt::fake();

        clear();

        Prompt::assertOutputContains("\033[H\033[J");
    }
}
