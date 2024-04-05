<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

use Cake\Chronos\Chronos;

final class ShipmentDto
{
    private String $orderNumber;

    private String $externalOrderNumber;

    private String $receivedOrderNumber;

    private Chronos $createdDate;

    public function __construct(
        String $orderNumber,
        String $externalOrderNumber,
        String $receivedOrderNumber,
        Chronos $createdDate,
    ) {
        $this->orderNumber = $orderNumber;
        $this->externalOrderNumber = $externalOrderNumber;
        $this->receivedOrderNumber = $receivedOrderNumber;
        $this->createdDate = $createdDate;
    }

    public function orderNumber(): String
    {
        return $this->orderNumber;
    }

    public function externalOrderNumber(): String
    {
        return $this->externalOrderNumber;
    }

    public function receivedOrderNumber(): String
    {
        return $this->receivedOrderNumber;
    }

    public function createdDate(): Chronos
    {
        return $this->createdDate;
    }
}
