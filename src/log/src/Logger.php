<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Log;

use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use Psr\Log\LoggerInterface;
use Stringable;

class Logger implements LoggerInterface
{
    /**
     * Any context to be added to logs.
     */
    protected array $context = [];

    /**
     * Create a new log writer instance.
     */
    public function __construct(
        protected LoggerInterface $logger
    ) {
    }

    /**
     * Log an emergency message to the logs.
     *
     * @param string $message
     */
    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log an alert message to the logs.
     *
     * @param string $message
     */
    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a critical message to the logs.
     *
     * @param string $message
     */
    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log an error message to the logs.
     *
     * @param string $message
     */
    public function error(string|Stringable $message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a warning message to the logs.
     *
     * @param string $message
     */
    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a notice to the logs.
     *
     * @param string $message
     */
    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log an informational message to the logs.
     *
     * @param string $message
     */
    public function info(string|Stringable $message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a debug message to the logs.
     *
     * @param string $message
     */
    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a message to the logs.
     *
     * @param string $level
     * @param string $message
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->writeLog($level, $message, $context);
    }

    /**
     * Dynamically pass log calls into the writer.
     *
     * @param string $level
     * @param string $message
     */
    public function write($level, string|Stringable $message, array $context = []): void
    {
        $this->writeLog($level, $message, $context);
    }

    /**
     * Write a message to the log.
     *
     * @param string $level
     * @param string $message
     */
    protected function writeLog($level, string|Stringable $message, array $context): void
    {
        $this->logger->{$level}(
            $message = $this->formatMessage($message),
            $context = $context
        );
    }

    /**
     * Format the parameters for the logger.
     *
     * @param mixed $message
     * @return mixed
     */
    protected function formatMessage($message)
    {
        if (is_array($message)) {
            return var_export($message, true);
        }
        if ($message instanceof Jsonable) {
            return $message->toJson();
        }
        if ($message instanceof Arrayable) {
            return var_export($message->toArray(), true);
        }

        return $message;
    }

    /**
     * Get the underlying logger implementation.
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Dynamically proxy method calls to the underlying logger.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->logger->{$method}(...$parameters);
    }
}
