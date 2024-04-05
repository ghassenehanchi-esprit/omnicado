<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Transforming;

use Elasticr\ServiceBus\ServiceBus\Model\ProductQuantityInStockDto;

final class ProductQuantityInStockTransformer
{
    /**
     * @return array<string, mixed>
     */
    public function transform(ProductQuantityInStockDto $quantityInStockDto): array
    {
        return [
            'productCode' => $quantityInStockDto->sku(),
            'realStock' => $quantityInStockDto->realStock(),
        ];
    }
}
