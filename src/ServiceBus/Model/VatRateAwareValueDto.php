<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class VatRateAwareValueDto
{
    private float $withoutVat;

    private float $withVat;

    private float $vatRate;

    public function __construct(float $withoutVat, float $withVat, float $vatRate)
    {
        $this->withoutVat = $withoutVat;
        $this->withVat = $withVat;
        $this->vatRate = $vatRate;
    }

    public function value(): ValueDto
    {
        return new ValueDto($this->withoutVat(), $this->withVat());
    }

    public function withoutVat(): float
    {
        return $this->withoutVat;
    }

    public function withVat(): float
    {
        return $this->withVat;
    }

    public function vat(): float
    {
        return round($this->withVat() - $this->withoutVat(), 2);
    }

    public function vatRate(): float
    {
        return $this->vatRate;
    }
}
