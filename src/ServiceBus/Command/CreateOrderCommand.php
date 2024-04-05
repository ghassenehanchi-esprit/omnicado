<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Command;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;
use Elasticr\ServiceBus\ServiceBus\Model\OrderDto;

final class CreateOrderCommand implements CommandContract
{
    private OrderDto $order;

    private int $customerId;

    public function __construct(OrderDto $order, int $customerId)
    {
        $this->order = $order;
        $this->customerId = $customerId;
    }

    public function order(): OrderDto
    {
        return $this->order;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }
}
