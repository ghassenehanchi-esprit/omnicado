<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Eso9;

use Elasticr\ServiceBus\Eso9\Contract\Eso9OrderCouponTransformerContract;

final class TestingEso9OrderCouponTransformer implements Eso9OrderCouponTransformerContract
{
    public function supports(int $customerId): bool
    {
        return $customerId === 1;
    }

    /**
     * @param array<mixed, mixed> $eso9CouponData
     * @return array<mixed, mixed> $eso9CouponData
     */
    public function transform(array $eso9CouponData): array
    {
        $eso9CouponData['sku'] = 'Changed sku';

        return $eso9CouponData;
    }
}
