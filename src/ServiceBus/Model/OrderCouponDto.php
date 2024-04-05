<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class OrderCouponDto
{
    private string $code;

    private string $name;

    private float $price;

    private float $priceWithoutVat;

    private float $taxRate;

    public function __construct(string $code, string $name, float $price, float $priceWithoutVat, float $taxRate)
    {
        $this->code = $code;
        $this->name = $name;
        $this->price = $price;
        $this->priceWithoutVat = $priceWithoutVat;
        $this->taxRate = $taxRate;
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
}
