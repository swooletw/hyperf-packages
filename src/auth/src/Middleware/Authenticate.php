<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SwooleTW\Hyperf\Auth\AuthenticationException;
use SwooleTW\Hyperf\Auth\AuthManager;

class Authenticate implements MiddlewareInterface
{
    public function __construct(
        protected AuthManager $auth
    ) {
    }

    public static function using(string ...$guards): string
    {
        return static::class . ':' . implode(',', $guards);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler, string ...$guards): ResponseInterface
    {
        $this->authenticate($request, $guards);

        return $handler->handle($request);
    }

    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param \Psr\Http\Server\RequestHandlerInterface $request
     *
     * @throws \SwooleTW\Hyperf\Auth\AuthenticationException
     */
    protected function authenticate($request, array $guards): void
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                $this->auth->shouldUse($guard);
                return;
            }
        }

        $this->unauthenticated($request, $guards);
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param \Psr\Http\Server\RequestHandlerInterface $request
     *
     * @throws \SwooleTW\Hyperf\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards): void
    {
        throw new AuthenticationException(
            'Unauthenticated.',
            $guards
        );
    }
}
