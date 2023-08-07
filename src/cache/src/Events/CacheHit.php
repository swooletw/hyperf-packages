<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache\Events;

class CacheHit extends CacheEvent
{
    /**
     * The value that was retrieved.
     *
     * @var mixed
     */
    public $value;

    /**
     * Create a new event instance.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __construct($key, $value, array $tags = [])
    {
        parent::__construct($key, $tags);

        $this->value = $value;
    }
}
