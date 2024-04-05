<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class ShippingMethodDto
{
    private string $code;

    private string $name;

    private float $price;

    private float $priceWithoutVat;

    private float $taxRate;

    private ?string $branchId;

    public function __construct(string $code, string $name, VatRateAwareValueDto $price, ?string $branchId)
    {
        $this->code = $code;
        $this->name = $name;
        $this->price = $price->withVat();
        $this->priceWithoutVat = $price->withoutVat();
        $this->taxRate = $price->vatRate();
        $this->branchId = $branchId;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): float
    {
        return $this->price;
    }

    public function priceWithoutVat(): float
    {
        return $this->priceWithoutVat;
    }

    public function taxRate(): float
    {
        return $this->taxRate;
    }

    public function branchId(): ?string
    {
        return $this->branchId;
    }
}
