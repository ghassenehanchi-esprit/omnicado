<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Transforming;

use Elasticr\ServiceBus\ServiceBus\Model\OrderTrackingInfoDto;

final class OrderTrackingInfoTransformer
{
    /**
     * @return array<string, mixed>
     */
    public function transform(OrderTrackingInfoDto $trackingInfoDto): array
    {
        $infoAsArray = [];

        if ($trackingInfoDto->trackingNumber() !== null) {
            $infoAsArray['trackingNumber'] = $trackingInfoDto->trackingNumber();
        }

        return $infoAsArray;
    }
}
