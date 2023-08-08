<?php

namespace SwooleTW\Hyperf\Foundation\Console\Contracts;

use SwooleTW\Hyperf\Foundation\Console\Scheduling\Schedule;

interface Kernel
{
    public function schedule(Schedule $schedule): void;
}