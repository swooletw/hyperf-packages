<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth\Middleware;

use SwooleTW\Hyperf\Auth\AuthManager;
use SwooleTW\Hyperf\Auth\AuthenticationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Authenticate implements MiddlewareInterface
{
    protected array $guards = [null];

    public function __construct(
        protected AuthManager $auth
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->authenticate($request, $this->guards);

        return $handler->handle($request);
    }

    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param  \Psr\Http\Server\RequestHandlerInterface  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate($request, array $guards = []): void
    {
        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        $this->unauthenticated($request, $guards);
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param  \Psr\Http\Server\RequestHandlerInterface  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards): void
    {
        throw new AuthenticationException(
            'Unauthenticated.', $guards
        );
    }
}
