<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Command;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;

final class GetAndUpdateAdviceStatusCommand implements CommandContract
{
    private string $adviceId;

    private int $customerId;

    public function __construct(string $adviceId, int $customerId)
    {
        $this->adviceId = $adviceId;
        $this->customerId = $customerId;
    }

    public function adviceId(): string
    {
        return $this->adviceId;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }
}
