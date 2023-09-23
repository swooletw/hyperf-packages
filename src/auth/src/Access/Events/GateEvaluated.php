<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Auth\Access\Events;

use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;

class GateEvaluated
{
    /**
     * The authenticatable model.
     */
    public ?Authenticatable $user;

    /**
     * The ability being evaluated.
     */
    public string $ability;

    /**
     * The result of the evaluation.
     */
    public ?bool $result;

    /**
     * The arguments given during evaluation.
     */
    public array $arguments;

    /**
     * Create a new event instance.
     */
    public function __construct(?Authenticatable $user, string $ability, ?bool $result, array $arguments)
    {
        $this->user = $user;
        $this->ability = $ability;
        $this->result = $result;
        $this->arguments = $arguments;
    }
}
