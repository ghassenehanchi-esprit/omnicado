<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class StockItemDto
{
    private string $productId;

    private string $stock;

    private float $quantity;

    public function __construct(string $productId, string $stock, float $quantity)
    {
        $this->productId = $productId;
        $this->stock = $stock;
        $this->quantity = $quantity;
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function stock(): string
    {
        return $this->stock;
    }

    public function quantity(): float
    {
        return $this->quantity;
    }
}
