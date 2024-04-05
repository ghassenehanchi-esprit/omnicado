<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Factory;

use Elasticr\ServiceBus\ServiceBus\Model\OrderItemDto;

final class ShoptetOrderItemDtoFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public function createFromApiResponse(array $data): OrderItemDto
    {
        return new OrderItemDto(
            (int) $data['itemId'],
            $data['code'],
            $data['name'],
            (float) $data['amount'],
            $data['amountUnit'],
            (float) $data['itemPrice']['withVat'],
            (float) $data['itemPrice']['withoutVat'],
            (float) $data['itemPrice']['vatRate'],
            (float) $data['itemPrice']['vat'],
            $data['supplierName'],
            (float) $data['weight'],
            0
        );
    }
}
