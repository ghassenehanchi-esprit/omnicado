<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Factory;

use Elasticr\ServiceBus\ServiceBus\Model\OrderCouponDto;

final class OrderCouponDtoFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public function createFromApiResponse(array $data): OrderCouponDto
    {
        return new OrderCouponDto(
            $data['code'] ?? '',
            $data['name'],
            (float) $data['itemPrice']['withVat'],
            (float) $data['itemPrice']['withoutVat'],
            (float) $data['itemPrice']['vatRate'],
        );
    }
}
