<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Console\Scheduling;

use Carbon\Carbon;
use Hyperf\Crontab\Crontab as HyperfCrontab;

class Crontab extends HyperfCrontab
{
    use ManagesFrequencies;

    protected ?string $rule = '* * * * *';

    public function name(?string $name): static
    {
        return $this->setName($name);
    }

    public function rule(?string $rule): static
    {
        return $this->setRule($rule);
    }

    public function mutexPool(string $mutexPool): static
    {
        return $this->setMutexPool($mutexPool);
    }

    public function mutexExpires(int $mutexExpires): static
    {
        return $this->setMutexExpires($mutexExpires);
    }

    public function callback(mixed $callback): static
    {
        return $this->setCallback($callback);
    }

    public function memo(?string $memo): static
    {
        return $this->setMemo($memo);
    }

    public function type(string $type): static
    {
        return $this->setType($type);
    }

    public function executeTime(Carbon $executeTime): static
    {
        return $this->setExecuteTime($executeTime);
    }

    public function enable(bool $enable = true): static
    {
        return $this->setEnable($enable);
    }

    public function withoutOverlapping(int $minutes = 1440): static
    {
        $this->setSingleton(true);
        $this->setMutexExpires($minutes * 60);

        return $this;
    }

    public function onOneServer(bool $onOneServer = true): static
    {
        return $this->setOnOneServer($onOneServer);
    }

    public function singleton(bool $singleton = true): static
    {
        return $this->setSingleton($singleton);
    }
}
