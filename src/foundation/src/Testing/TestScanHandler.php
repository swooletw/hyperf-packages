<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Testing;

use Hyperf\Di\ScanHandler\ScanHandlerInterface;
use Hyperf\Di\ScanHandler\Scanned;

class TestScanHandler implements ScanHandlerInterface
{
    public function scan(): Scanned
    {
        return new Scanned(true);
    }
}
