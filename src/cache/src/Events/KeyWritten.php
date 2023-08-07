<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Cache\Events;

class KeyWritten extends CacheEvent
{
    /**
     * The value that was written.
     *
     * @var mixed
     */
    public $value;

    /**
     * The number of seconds the key should be valid.
     *
     * @var null|int
     */
    public $seconds;

    /**
     * Create a new event instance.
     *
     * @param string $key
     * @param mixed $value
     * @param null|int $seconds
     * @param array $tags
     */
    public function __construct($key, $value, $seconds = null, $tags = [])
    {
        parent::__construct($key, $tags);

        $this->value = $value;
        $this->seconds = $seconds;
    }
}
