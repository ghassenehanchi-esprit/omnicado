<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class ProductQuantityInStockDto
{
    private string $sku;

    private float $realStock;

    private float $quantity;

    private ?string $stock;

    public function __construct(string $sku, float $realStock, float $quantity, string $stock = null)
    {
        $this->sku = $sku;
        $this->realStock = $realStock;
        $this->quantity = $quantity;
        $this->stock = $stock;
    }

    public function sku(): string
    {
        return $this->sku;
    }

    public function realStock(): float
    {
        return $this->realStock;
    }

    public function quantity(): float
    {
        return $this->quantity;
    }

    public function stock(): ?string
    {
        return $this->stock;
    }
}
