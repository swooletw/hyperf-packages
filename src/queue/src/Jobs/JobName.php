<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Queue\Jobs;

use Hyperf\Stringable\Str;
use SwooleTW\Hyperf\Queue\CallQueuedHandler;

class JobName
{
    /**
     * Parse the given job name into a class / method array.
     */
    public static function parse(string $job): array
    {
        $result = Str::parseCallback($job, 'fire');

        // Make CallQueuedHandler compatible with Laravel's Queue
        if ($result[0] === 'Illuminate\Queue\CallQueuedHandler') {
            $result[0] = CallQueuedHandler::class;
        }

        return $result;
    }

    /**
     * Get the resolved name of the queued job class.
     */
    public static function resolve(string $name, array $payload): string
    {
        if (! empty($payload['displayName'])) {
            return $payload['displayName'];
        }

        return $name;
    }
}
