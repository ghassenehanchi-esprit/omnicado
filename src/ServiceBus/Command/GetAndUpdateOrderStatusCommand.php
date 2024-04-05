<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Command;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;

final class GetAndUpdateOrderStatusCommand implements CommandContract
{
    private string $orderNumber;

    private int $customerId;

    public function __construct(string $orderNumber, int $customerId)
    {
        $this->orderNumber = $orderNumber;
        $this->customerId = $customerId;
    }

    public function orderNumber(): string
    {
        return $this->orderNumber;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }
}
