<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Command;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;
use Elasticr\ServiceBus\ServiceBus\Model\OrderStatusDto;

final class UpdateSellOrderStatusCommand implements CommandContract
{
    private int $customerId;

    private OrderStatusDto $orderStatusDto;

    public function __construct(OrderStatusDto $orderStatusDto, int $customerId)
    {
        $this->customerId = $customerId;
        $this->orderStatusDto = $orderStatusDto;
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
