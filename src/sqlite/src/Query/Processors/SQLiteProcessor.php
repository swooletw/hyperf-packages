<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Database\Query\Processors;

use Hyperf\Database\Query\Processors\Processor;

class SQLiteProcessor extends Processor
{
    /**
     * Process the results of a column listing query.
     *
     * @param  array  $results
     * @return array
     */
    public function processColumnListing($results): array
    {
        return array_map(function ($result) {
            return ((object) $result)->name;
        }, $results);
    }
}
