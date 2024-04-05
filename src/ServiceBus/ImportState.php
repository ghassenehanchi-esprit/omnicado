<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus;

use Cake\Chronos\Chronos;
use UnexpectedValueException;

final class ImportState
{
    private ?Chronos $startedTime = null;

    private bool $hasFailed = false;

    public function changeStartedTime(Chronos $startedTime): void
    {
        $this->startedTime = $startedTime;
    }

    public function startedTime(): Chronos
    {
        return $this->startedTime ?? throw new UnexpectedValueException();
    }

    public function finishedSuccessfully(): void
    {
        $this->hasFailed = false;
    }

    public function finishedWithError(): void
    {
        $this->hasFailed = true;
    }

    public function hasFailed(): bool
    {
        return $this->hasFailed;
    }
}
