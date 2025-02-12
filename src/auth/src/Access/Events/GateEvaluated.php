<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth\Access\Events;

use SwooleTW\Hyperf\Auth\Access\Response;
use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;

class GateEvaluated
{
    /**
     * Create a new event instance.
     *
     * @param null|Authenticatable $user the authenticatable model
     * @param string $ability the ability being evaluated
     * @param null|bool|Response $result the result of the evaluation
     * @param array $arguments the arguments given during evaluation
     */
    public function __construct(
        public ?Authenticatable $user,
        public string $ability,
        public null|bool|Response $result,
        public array $arguments
    ) {
    }
}
