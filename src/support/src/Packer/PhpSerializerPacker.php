<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Packer;

use Hyperf\Contract\PackerInterface;

class PhpSerializerPacker implements PackerInterface
{
    public function pack($data): string
    {
        return is_numeric($data) && ! in_array($data, [INF, -INF]) && ! is_nan($data) ? $data : serialize($data);
    }

    public function unpack(string $data)
    {
        if ($data === null || $data === false) {
            return null;
        }
        return is_numeric($data) ? $data : unserialize((string) $data);
    }
}
