<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Command;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;
use Elasticr\ServiceBus\ServiceBus\Model\ProductDto;

final class CreateProductCommand implements CommandContract
{
    private ProductDto $product;

    private int $customerId;

    public function __construct(ProductDto $product, int $customerId)
    {
        $this->product = $product;
        $this->customerId = $customerId;
    }

    public function product(): ProductDto
    {
        return $this->product;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }
}
