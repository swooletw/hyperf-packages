<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Testing\Http;

use Hyperf\Testing\Http\TestResponse as HyperfTestResponse;

class TestResponse extends HyperfTestResponse
{
    /**
     * Dump the content from the response and end the script.
     *
     * @return never
     */
    public function dd()
    {
        $this->dump();

        exit(1);
    }

    /**
     * Dump the content from the response.
     *
     * @return $this
     */
    public function dump()
    {
        $content = $this->getContent();

        $json = json_decode($content);

        if (json_last_error() === JSON_ERROR_NONE) {
            $content = $json;
        }

        dump($content);

        return $this;
    }

    /**
     * Dump the headers from the response.
     *
     * @return $this
     */
    public function dumpHeaders()
    {
        dump($this->headers->all());

        return $this;
    }
}
