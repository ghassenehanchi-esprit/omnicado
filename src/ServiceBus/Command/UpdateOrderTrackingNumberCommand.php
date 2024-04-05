<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Command;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;
use Elasticr\ServiceBus\ServiceBus\Model\OrderTrackingInfoDto;

final class UpdateOrderTrackingNumberCommand implements CommandContract
{
    private OrderTrackingInfoDto $orderTrackingInfoDto;

    private int $customerId;

    public function __construct(OrderTrackingInfoDto $orderTrackingInfoDto, int $customerId)
    {
        $this->orderTrackingInfoDto = $orderTrackingInfoDto;
        $this->customerId = $customerId;
    }

    public function orderTrackingInfoDto(): OrderTrackingInfoDto
    {
        return $this->orderTrackingInfoDto;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }
}
