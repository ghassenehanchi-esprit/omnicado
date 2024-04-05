<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class OrderItemDto
{
    private int $id;

    private ?string $sku;

    private string $name;

    private float $amount;

    private ?string $amountUnit;

    private float $price;

    private float $priceWithoutVat;

    private float $taxRate;

    private float $tax;

    private ?string $supplierName;

    private float $weight;

    private int $rowNumber;

    public function __construct(
        int $id,
        ?string $sku,
        string $name,
        float $amount,
        ?string $amountUnit,
        float $price,
        float $priceWithoutVat,
        float $taxRate,
        float $tax,
        ?string $supplierName,
        float $weight,
        int $rowNumber
    ) {
        $this->id = $id;
        $this->sku = $sku;
        $this->name = $name;
        $this->amount = $amount;
        $this->amountUnit = $amountUnit;
        $this->price = $price;
        $this->priceWithoutVat = $priceWithoutVat;
        $this->taxRate = $taxRate;
        $this->tax = $tax;
        $this->supplierName = $supplierName;
        $this->weight = $weight;
        $this->rowNumber = $rowNumber;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function sku(): ?string
    {
        return $this->sku;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function amount(): float
    {
        return $this->amount;
    }

    public function amountUnit(): ?string
    {
        return $this->amountUnit;
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

    public function tax(): float
    {
        return $this->tax;
    }

    public function supplierName(): ?string
    {
        return $this->supplierName;
    }

    public function weight(): float
    {
        return $this->weight;
    }

    public function rowNumber(): int
    {
        return $this->rowNumber;
    }

    public function changeAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function changePrice(float $price): void
    {
        $this->price = $price;
    }

    public function changePriceWithoutVat(float $priceWithoutVat): void
    {
        $this->priceWithoutVat = $priceWithoutVat;
    }

    public function changeTax(float $tax): void
    {
        $this->tax = $tax;
    }

    public function changeWeight(float $weight): void
    {
        $this->weight = $weight;
    }

    public function unitPriceWithoutVat(): float
    {
        return round($this->priceWithoutVat() / $this->amount(), 2);
    }
}
