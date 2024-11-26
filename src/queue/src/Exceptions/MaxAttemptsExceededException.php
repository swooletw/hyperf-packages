<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Queue\Exceptions;

use RuntimeException;
use SwooleTW\Hyperf\Queue\Contracts\Job;

use function Hyperf\Tappable\tap;

class MaxAttemptsExceededException extends RuntimeException
{
    /**
     * The job instance.
     */
    public ?Job $job = null;

    /**
     * Create a new instance for the job.
     */
    public static function forJob(Job $job): static
    {
        return tap(new static($job->resolveName() . ' has been attempted too many times.'), function ($e) use ($job) {
            $e->job = $job;
        });
    }
}
