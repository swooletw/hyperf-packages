<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Telescope;

use Hyperf\Command\Event\AfterExecute as AfterExecuteCommand;
use Hyperf\Command\Event\BeforeHandle as BeforeHandleCommand;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Event\RequestReceived;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Http\Contracts\RequestContract;
use SwooleTW\Hyperf\Queue\Events\JobExceptionOccurred;
use SwooleTW\Hyperf\Queue\Events\JobFailed;
use SwooleTW\Hyperf\Queue\Events\JobProcessed;
use SwooleTW\Hyperf\Queue\Events\JobProcessing;
use SwooleTW\Hyperf\Telescope\Contracts\EntriesRepository;

trait ListensForStorageOpportunities
{
    public const PROCESSING_JOBS = 'telescope.processing_jobs';

    /**
     * Register listeners that store the recorded Telescope entries.
     */
    public static function listenForStorageOpportunities(ContainerInterface $app): void
    {
        static::recordEntriesForRequests($app);
        static::manageRecordingStateForCommands($app);
        static::storeEntriesAfterWorkerLoop($app);
    }

    /**
     * Record the entries in queue before the request termination.
     */
    public static function recordEntriesForRequests(ContainerInterface $app): void
    {
        $app->get(EventDispatcherInterface::class)
            ->listen(RequestReceived::class, function ($event) use ($app) {
                if (static::requestIsToApprovedUri($app->get(RequestContract::class))) {
                    static::startRecording();
                }
            });
    }

    /**
     * Manage starting and stopping the recording state for commands.
     */
    public static function manageRecordingStateForCommands(ContainerInterface $app): void
    {
        $app->get(EventDispatcherInterface::class)
            ->listen(BeforeHandleCommand::class, function () {
                if (static::runningApprovedArtisanCommand()) {
                    static::startRecording();
                }
            });
        $app->get(EventDispatcherInterface::class)
            ->listen(AfterExecuteCommand::class, function () use ($app) {
                static::store(
                    $app->get(EntriesRepository::class)
                );
            });
    }

    /**
     * Get the current processing jobs.
     */
    protected static function getProcessingJobs(): array
    {
        return Context::get(static::PROCESSING_JOBS, []);
    }

    /**
     * Add a processing job to the stack.
     */
    protected static function addProcessingJob(): array
    {
        return Context::override(static::PROCESSING_JOBS, function ($jobs) {
            $jobs = $jobs ?? [];
            $jobs[] = true;

            return $jobs;
        });
    }

    /**
     * Pop the last processing job from the stack.
     */
    protected static function popProcessingJob(): array
    {
        return Context::override(static::PROCESSING_JOBS, function ($jobs) {
            $jobs = $jobs ?? [];
            array_pop($jobs);

            return $jobs;
        });
    }

    /**
     * Store entries after the queue worker loops.
     */
    protected static function storeEntriesAfterWorkerLoop(ContainerInterface $app): void
    {
        $event = $app->get(EventDispatcherInterface::class);
        $event->listen(JobProcessing::class, function ($event) {
            if ($event->connectionName !== 'sync') {
                static::startRecording();
                static::addProcessingJob();
            }
        });

        $event->listen(JobProcessed::class, function ($event) use ($app) {
            static::storeIfDoneProcessingJob($event, $app);
        });

        $event->listen(JobFailed::class, function ($event) use ($app) {
            static::storeIfDoneProcessingJob($event, $app);
        });

        $event->listen(JobExceptionOccurred::class, function () {
            static::popProcessingJob();
        });
    }

    /**
     * Store the recorded entries if totally done processing the current job.
     */
    protected static function storeIfDoneProcessingJob(JobFailed|JobProcessed $event, ContainerInterface $app): void
    {
        static::popProcessingJob();

        if (empty(static::getProcessingJobs()) && $event->connectionName !== 'sync') {
            static::store($app->get(EntriesRepository::class));
            static::stopRecording();
        }
    }
}
