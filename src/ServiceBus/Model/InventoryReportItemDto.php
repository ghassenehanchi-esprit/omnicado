<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class InventoryReportItemDto
{
    private string $productId;

    private string $productName;

    private string $stock;

    private float $quantityInStock;

    private float $quantityReserved;

    private float $quantityAvailable;

    public function __construct(string $productId, string $productName, string $stock, float $quantityInStock, float $quantityReserved, float $quantityAvailable)
    {
        $this->productId = $productId;
        $this->stock = $stock;
        $this->quantityInStock = $quantityInStock;
        $this->quantityReserved = $quantityReserved;
        $this->productName = $productName;
        $this->quantityAvailable = $quantityAvailable;
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function stock(): string
    {
        return $this->stock;
    }

    public function quantityInStock(): float
    {
        return $this->quantityInStock;
    }

    public function quantityReserved(): float
    {
        return $this->quantityReserved;
    }

    public function quantityAvailable(): float
    {
        return $this->quantityAvailable;
    }
}
