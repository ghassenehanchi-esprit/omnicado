<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Command;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;
use Elasticr\ServiceBus\ServiceBus\Model\OrderStatusDto;

final class UpdateOrderStatusCommand implements CommandContract
{
    private OrderStatusDto $orderStatusDto;

    private int $customerId;

    public function __construct(OrderStatusDto $orderStatusDto, int $customerId)
    {
        $this->orderStatusDto = $orderStatusDto;
        $this->customerId = $customerId;
    }

    public function orderStatusDto(): OrderStatusDto
    {
        return $this->orderStatusDto;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }
}
