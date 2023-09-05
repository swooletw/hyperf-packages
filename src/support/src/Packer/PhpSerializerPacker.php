<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Packer;

use Hyperf\Contract\PackerInterface;

class PhpSerializerPacker implements PackerInterface
{
    public function pack($data): string
    {
        // is_nan() doesn't work in strict mode
        return is_numeric($value) && ! in_array($value, [INF, -INF]) && ($value === $value) ? $value : serialize($value);
    }

    public function unpack(string $data)
    {
        if ($data === null || $data === false) {
            return null;
        }
        return is_numeric($data) ? $data : unserialize((string) $data);
    }
}
