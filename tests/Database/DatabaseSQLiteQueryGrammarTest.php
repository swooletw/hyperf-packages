<?php

namespace SwooleTW\Hyperf\Tests\Database;

use Hyperf\Database\Connection;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Database\Query\Grammars\SQLiteGrammar;

class DatabaseSQLiteQueryGrammarTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    // public function testToRawSql()
    // {
    //     $connection = m::mock(Connection::class);
    //     $connection->shouldReceive('escape')->with('foo', false)->andReturn("'foo'");
    //     $grammar = new SQLiteGrammar;
    //     $grammar->setConnection($connection);

    //     $query = $grammar->substituteBindingsIntoRawSql(
    //         'select * from "users" where \'Hello\'\'World?\' IS NOT NULL AND "email" = ?',
    //         ['foo'],
    //     );

    //     $this->assertSame('select * from "users" where \'Hello\'\'World?\' IS NOT NULL AND "email" = \'foo\'', $query);
    // }
}
