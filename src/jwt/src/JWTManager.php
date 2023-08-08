<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\JWT;

use Hyperf\Collection\Collection;
use Hyperf\Stringable\Str;
use SwooleTW\Hyperf\JWT\Blacklist;
use SwooleTW\Hyperf\JWT\Contracts\JWTContract;
use SwooleTW\Hyperf\JWT\Contracts\ValidationContract;
use SwooleTW\Hyperf\JWT\Exceptions\JWTException;
use SwooleTW\Hyperf\JWT\Exceptions\TokenBlacklistedException;

class JWTManager
{
    protected bool $blacklistEnabled = true;
    protected array $persistentClaims = [];

    public function __construct(
        protected JWTContract $provider,
        protected Blacklist $blacklist,
        protected array $config = [],
        protected array $validations = [],
    ) {
        $this->blacklistEnabled = $config['blacklist_enabled'] ?? $this->blacklistEnabled;
        $this->persistentClaims = $config['persistent_claims'] ?? $this->persistentClaims;
        $this->blacklist->setGracePeriod($config['blacklist_grace_period'] ?? 0);
        $this->blacklist->setRefreshTTL($config['refresh_ttl'] ?? 20160);
    }

    public function encode(array $payload): string
    {
        if ($this->blacklistEnabled) {
            $payload['jti'] = (string) Str::uuid();
        }

        return $this->provider->encode($payload);
    }

    public function decode(string $token, bool $validate = true, bool $checkBlacklist = true): array
    {
        $payload = $this->provider->decode($token);

        if ($validate) {
            $this->validatePayload($payload);
        }

        if ($this->blacklistEnabled && $checkBlacklist && $this->blacklist->has($payload)) {
            throw new TokenBlacklistedException('The token has been blacklisted');
        }

        return $payload;
    }

    protected function validatePayload(array $payload): void
    {
        foreach ($this->config['validations'] as $validation) {
            $this->getValidation($validation)
                ->validate($payload);
        }
    }

    protected function getValidation(string $class): ValidationContract
    {
        if ($validation = ($this->validations[$class] ?? null)) {
            return $validation;
        }

        return $this->validations[$class] = new $class($this->config);
    }

    public function refresh(string $token, $forceForever = false): string
    {
        $claims = $this->buildRefreshClaims($this->decode($token));

        if ($this->blacklistEnabled) {
            // Invalidate old token
            $this->invalidate($token, $forceForever);
        }

        // Return the new token
        return $this->encode($claims);
    }

    public function invalidate(string $token, bool $forceForever = false)
    {
        if (! $this->blacklistEnabled) {
            throw new JWTException('You must have the blacklist enabled to invalidate a token.');
        }

        return call_user_func(
            [$this->blacklist, $forceForever ? 'addForever' : 'add'],
            $this->decode($token, false)
        );
    }

    protected function buildRefreshClaims(array $payload): array
    {
        // Get the claims to be persisted from the payload
        $persistentClaims = Collection::make($payload)
            ->only($this->persistentClaims)
            ->toArray();

        // persist the relevant claims
        return array_merge(
            $persistentClaims,
            [
                'sub' => $payload['sub'],
                'iat' => $payload['iat'],
            ]
        );
    }

    public function getJWTProvider(): JWTContract
    {
        return $this->provider;
    }

    public function getBlacklist(): Blacklist
    {
        return $this->blacklist;
    }

    public function setPersistentClaims(array $claims): static
    {
        $this->persistentClaims = $claims;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): static
    {
        $this->config = $config;

        return $this;
    }
}
