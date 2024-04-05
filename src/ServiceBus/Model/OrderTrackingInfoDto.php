<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

use Cake\Chronos\Chronos;

final class OrderTrackingInfoDto
{
    private string $orderNumber;

    private ?string $trackingNumber;

    private ?string $trackingUrl;

    private ?Chronos $shippingDate;

    public function __construct(string $orderNumber, ?string $trackingNumber, ?string $trackingUrl, ?Chronos $shippingDate = null)
    {
        $this->trackingNumber = $trackingNumber;
        $this->trackingUrl = $trackingUrl;
        $this->orderNumber = $orderNumber;
        $this->shippingDate = $shippingDate;
    }

    public function orderNumber(): string
    {
        return $this->orderNumber;
    }

    public function trackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function trackingUrl(): ?string
    {
        return $this->trackingUrl;
    }

    public function shippingDate(): ?Chronos
    {
        return $this->shippingDate;
    }
}
