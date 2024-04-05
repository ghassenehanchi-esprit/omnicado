<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Factory;

use Elasticr\ServiceBus\ServiceBus\Model\ShippingMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;

final class ShoptetShippingMethodDtoFactory
{
    /**
     * @param array<string, string>|null $data
     * @param array<string, string> $priceData
     */
    public function createFromApiResponse(?array $data, array $priceData, ?string $branchId): ShippingMethodDto
    {
        if ($data === null || !count($priceData)) {
            return new ShippingMethodDto('', '', new VatRateAwareValueDto(0, 0, 0), null);
        }

        return new ShippingMethodDto(
            $data['guid'],
            $data['name'],
            new VatRateAwareValueDto((float) ($priceData['withoutVat'] ?? 0), (float) ($priceData['withVat'] ?? 0), (float) ($priceData['vatRate'] ?? 0)),
            $branchId
        );
    }
}
