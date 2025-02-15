<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Telescope\Aspects;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Stringable\Str;
use Psr\Http\Message\ResponseInterface;
use SwooleTW\Hyperf\Telescope\IncomingEntry;
use SwooleTW\Hyperf\Telescope\Telescope;
use Throwable;

/**
 * This file is originated from friendsofhyperf/telescope.
 */
class GuzzleHttpClientAspect extends AbstractAspect
{
    public array $classes = [
        Client::class . '::transfer',
    ];

    protected array $options = [];

    public function __construct(protected ConfigInterface $config)
    {
        $this->options = $config->get('telescope.aspects.' . static::class, []);
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // If the guzzle aspect is disabled, we will not record the request.
        if (! Telescope::$started
            || ! ($this->options['enabled'] ?? false)
            || ! Telescope::isRecording()
        ) {
            return $proceedingJoinPoint->process();
        }

        $options = $proceedingJoinPoint->arguments['keys']['options'] ?? [];
        $guzzleConfig = (fn () => $this->config ?? [])->call($proceedingJoinPoint->getInstance());

        // If the telescope_enabled option is set to false, we will not record the request.
        if (($options['telescope_enabled'] ?? null) === false
            || ($guzzleConfig['telescope_enabled'] ?? null) === false
        ) {
            return $proceedingJoinPoint->process();
        }

        // Add or override the on_stats option to record the request duration.
        $onStats = $options['on_stats'] ?? null;
        $proceedingJoinPoint->arguments['keys']['options']['on_stats'] = function (TransferStats $stats) use ($onStats) {
            try {
                $request = $stats->getRequest();
                $response = $stats->getResponse();
                $content = [
                    'method' => $request->getMethod(),
                    'uri' => (string) $request->getUri(),
                    'headers' => $request->getHeaders(),
                    'duration' => $stats->getTransferTime() * 1000,
                ];

                if ($response = $stats->getResponse()) {
                    $content['response_status'] = $response->getStatusCode();
                    $content['response_headers'] = $response->getHeaders();
                    $content['response'] = $this->getResponse($response);
                }

                Telescope::recordClientRequest(IncomingEntry::make($content));
            } catch (Throwable $exception) {
                // We will catch the exception to prevent the request from being interrupted.
            }

            if (is_callable($onStats)) {
                $onStats($stats);
            }
        };

        return $proceedingJoinPoint->process();
    }

    public function getResponse(ResponseInterface $response): ?string
    {
        $stream = $response->getBody();
        try {
            if ($stream->isSeekable()) {
                $stream->rewind();
            }

            $content = $stream->getContents();

            if (is_string($content)) {
                if (! $this->contentWithinLimits($content)) {
                    return 'Purged By Telescope';
                }
                if (is_array(json_decode($content, true))
                    && json_last_error() === JSON_ERROR_NONE
                ) {
                    return $this->contentWithinLimits($content) /* @phpstan-ignore-line */
                        ? $this->hideParameters(json_decode($content, true), Telescope::$hiddenResponseParameters)
                        : 'Purged By Telescope';
                }
                if (Str::startsWith(strtolower($response->getHeaderLine('content-type') ?: ''), 'text/plain')) {
                    return $this->contentWithinLimits($content) ? $content : 'Purged By Telescope'; /* @phpstan-ignore-line */
                }
            }

            if (empty($content)) {
                return 'Empty Response';
            }
        } catch (Throwable $e) {
            return 'Purged By Telescope: ' . $e->getMessage();
        } finally {
            if ($stream->isSeekable()) {
                $stream->rewind();
            }
        }

        return 'HTML Response';
    }

    protected function contentWithinLimits(string $content): bool
    {
        $limit = $this->options['size_limit'] ?? 64;

        return intdiv(mb_strlen($content), 1000) <= $limit;
    }

    /**
     * Hide the given parameters.
     */
    protected function hideParameters(array $data, array $hidden): array
    {
        foreach ($hidden as $parameter) {
            if (Arr::get($data, $parameter)) {
                Arr::set($data, $parameter, '********');
            }
        }

        return $data;
    }
}
