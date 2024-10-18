<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Filesystem;

use DateTimeInterface;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Hyperf\Collection\Arr;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter as FlysystemGoogleCloudAdapter;
use League\Flysystem\UnableToReadFile;
use RuntimeException;
use Throwable;

class GoogleCloudStorageAdapter extends FilesystemAdapter
{
    public const DEFAULT_API_ENDPOINT = 'https://storage.googleapis.com';

    public function __construct(
        FilesystemOperator $driver,
        FlysystemGoogleCloudAdapter $adapter,
        protected array $config,
        protected StorageClient $client
    ) {
        parent::__construct($driver, $adapter, $config);
    }

    /**
     * Get the URL for the file at the given path.
     *
     * @throws RuntimeException
     */
    public function url(string $path): string
    {
        $storageApiUri = Arr::get($this->config, 'storageApiUri')
            ?: static::DEFAULT_API_ENDPOINT . '/' . ltrim(Arr::get($this->config, 'bucket'), '/');

        if (Arr::get($this->config, 'storageApiUri')) {
            $storageApiUri = Arr::get($this->config, 'storageApiUri');
        }

        return $this->concatPathToUrl($storageApiUri, $this->prefixer->prefixPath($path));
    }

    /**
     * Get a temporary URL for the file at the given path.
     */
    public function temporaryUrl(string $path, DateTimeInterface $expiration, array $options = []): string
    {
        if (Arr::get($this->config, 'storageApiUri')) {
            $options['bucketBoundHostname'] = Arr::get($this->config, 'storageApiUri');
        }

        return $this->getBucket()->object($this->prefixer->prefixPath($path))->signedUrl($expiration, $options);
    }

    /**
     * Get a temporary upload URL for the file at the given path.
     */
    public function temporaryUploadUrl(string $path, DateTimeInterface $expiration, array $options = []): array|string
    {
        if (Arr::get($this->config, 'storageApiUri')) {
            $options['bucketBoundHostname'] = Arr::get($this->config, 'storageApiUri');
        }

        return $this->getBucket()->object($this->prefixer->prefixPath($path))->beginSignedUploadSession($options);
    }

    public function readStream(string $path): mixed
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        try {
            $options = ($this->config['stream_reads'] ?? false) ? ['restOptions' => ['stream' => true]] : [];
            $stream = $this->getBucket()->object($prefixedPath)->downloadAsStream($options)->detach();
        } catch (Throwable $exception) {
            throw UnableToReadFile::fromLocation($path, $exception->getMessage(), $exception);
        }

        if (! is_resource($stream)) {
            throw UnableToReadFile::fromLocation($path, 'Downloaded object does not contain a file resource.');
        }

        return $stream;
    }

    /**
     * Get the underlying GCS client.
     */
    public function getClient(): StorageClient
    {
        return $this->client;
    }

    private function getBucket(): Bucket
    {
        return $this->client->bucket(Arr::get($this->config, 'bucket'));
    }

    /**
     * Determine if temporary URLs can be generated.
     */
    public function providesTemporaryUrls(): bool
    {
        return true;
    }
}
