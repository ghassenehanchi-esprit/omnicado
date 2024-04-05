<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Factory;

use Elasticr\ServiceBus\ServiceBus\Model\PaymentMethodDto;
use Elasticr\ServiceBus\ServiceBus\Model\VatRateAwareValueDto;

final class ShoptetPaymentMethodDtoFactory
{
    /**
     * @param array<string, string>|null $data
     * @param array<string, string> $priceData
     */
    public function createFromApiResponse(?array $data, array $priceData): PaymentMethodDto
    {
        if ($data === null || !count($priceData)) {
            return new PaymentMethodDto('', '', new VatRateAwareValueDto(0, 0, 0));
        }

        return new PaymentMethodDto(
            $data['guid'],
            $data['name'],
            new VatRateAwareValueDto((float) ($priceData['withoutVat'] ?? 0), (float) ($priceData['withVat'] ?? 0), (float) ($priceData['vatRate'] ?? 0))
        );
    }
}
