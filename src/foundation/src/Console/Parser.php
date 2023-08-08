<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console;

class Parser
{
    public static function parse(string $input): array
    {
        $pattern = '/\-\-(?P<key>[^\s=]+)(?:=(?P<value>[^\s]*))?/';
        preg_match_all($pattern, $input, $matches, PREG_SET_ORDER);

        $options = [];
        foreach ($matches as $match) {
            $options["--{$match['key']}"] = $match['value'] ?? null;
        }

        $input = preg_replace($pattern, '', $input);
        $parts = array_values(array_filter(explode(' ', $input)));

        $command = array_shift($parts);
        $arguments = $parts;

        return [
            'command' => $command,
            'arguments' => $arguments,
            'options' => $options,
        ];
    }
}