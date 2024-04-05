<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Command;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;
use Elasticr\ServiceBus\ServiceBus\Model\ProductQuantityInStockDto;

final class UpdateProductsQuantityCommand implements CommandContract
{
    /**
     * @var ProductQuantityInStockDto[]
     */
    private array $productsQuantity;

    private int $customerId;

    /**
     * @param ProductQuantityInStockDto[]  $productsQuantity
     */
    public function __construct(array $productsQuantity, int $customerId)
    {
        $this->productsQuantity = $productsQuantity;
        $this->customerId = $customerId;
    }

    /**
     * @return ProductQuantityInStockDto[]
     */
    public function productsQuantity(): array
    {
        return $this->productsQuantity;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }
}
