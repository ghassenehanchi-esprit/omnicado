<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Command;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;
use Elasticr\ServiceBus\ServiceBus\Model\ProductDto;
use Elasticr\Support\Collection\ImmutableCollection;

final class CreateProductsCommand implements CommandContract
{
    /**
     * @var ImmutableCollection<int, ProductDto>
     */
    private ImmutableCollection $products;

    private int $customerId;

    /**
     * @param ImmutableCollection<int, ProductDto> $products
     */
    public function __construct(ImmutableCollection $products, int $customerId)
    {
        $this->products = $products;
        $this->customerId = $customerId;
    }

    /**
     * @return ImmutableCollection<int, ProductDto>
     */
    public function products(): ImmutableCollection
    {
        return $this->products;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }
}
