<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class OrderStatusDto
{
    private string $orderNumber;

    private string $status;

    public function __construct(string $orderNumber, string $status)
    {
        $this->orderNumber = $orderNumber;
        $this->status = $status;
    }

    public function orderNumber(): string
    {
        return $this->orderNumber;
    }

    public function status(): string
    {
        return $this->status;
    }
}
