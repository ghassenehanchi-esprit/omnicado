<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Transforming;

use Elasticr\ServiceBus\Shoptet\Model\OrderStatusDto;

final class OrderStatusTransformer
{
    /**
     * @return array<string, mixed>
     */
    public function transform(int $eshopId, OrderStatusDto $orderStatusDto): array
    {
        return [
            'statusId' => $orderStatusDto->id(),
        ];
    }
}
