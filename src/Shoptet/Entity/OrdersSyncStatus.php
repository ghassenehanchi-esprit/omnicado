<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Entity;

use Cake\Chronos\Chronos;

class OrdersSyncStatus
{
    private int $id;

    private int $customerId;

    private ?Chronos $lastProcessedOrderCreationTime = null;

    private ?Chronos $lastProcessedOrderUpdateStatusTime = null;

    public function __construct(int $customerId)
    {
        $this->id = 0;
        $this->customerId = $customerId;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }

    public function lastProcessedOrderCreationTime(): ?Chronos
    {
        return $this->lastProcessedOrderCreationTime;
    }

    public function updateLastProcessedOrderCreationTime(?Chronos $time): void
    {
        $this->lastProcessedOrderCreationTime = $time;
    }

    public function lastProcessedOrderUpdateStatusTime(): ?Chronos
    {
        return $this->lastProcessedOrderUpdateStatusTime;
    }

    public function updateLastProcessedOrderUpdateStatusTime(?Chronos $time): void
    {
        $this->lastProcessedOrderUpdateStatusTime = $time;
    }
}
