<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\App;

final class CommandResult
{
    private bool $isSuccessful = false;

    public function markAsSuccessful(): void
    {
        $this->isSuccessful = true;
    }

    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }
}
