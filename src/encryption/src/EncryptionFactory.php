<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Encryption;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;
use SwooleTW\Hyperf\Encryption\Encrypter;
use SwooleTW\Hyperf\Encryption\Exceptions\MissingAppKeyException;
use function Hyperf\Tappable\tap;

class EncryptionFactory
{
    public function __invoke(ContainerInterface $container): Encrypter
    {
        $config = $container->get(ConfigInterface::class)
            ->get('encryption', []);

        return new Encrypter(
            $this->parseKey($config),
            $config['cipher']
        );
    }

    /**
     * Parse the encryption key.
     *
     * @param  array  $config
     * @return string
     */
    protected function parseKey(array $config): string
    {
        if (Str::startsWith($key = $this->key($config), $prefix = 'base64:')) {
            $key = base64_decode(Str::after($key, $prefix));
        }

        return $key;
    }

    /**
     * Extract the encryption key from the given configuration.
     *
     * @param  array  $config
     * @return string
     *
     * @throws \SwooleTW\Hyperf\Encryption\Exceptions\MissingAppKeyException
     */
    protected function key(array $config): string
    {
        return tap($config['key'], function ($key) {
            if (empty($key)) {
                throw new MissingAppKeyException;
            }
        });
    }
}
