<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Factory;

use Elasticr\ServiceBus\Shoptet\ValueObject\ShoptetConfig;
use Elasticr\Support\Doctrine\Contract\DoctrineSerializableFactoryContract;

final class ShoptetConfigFactory implements DoctrineSerializableFactoryContract
{
    public function supports(string $type): bool
    {
        return $type === ShoptetConfig::TYPE_NAME;
    }

    public function create(array $data): ShoptetConfig
    {
        return new ShoptetConfig((int) $data['eshopId'], (int) $data['transferredOrderStatus'], (int) $data['errorOrderStatus'], $data['paidStatusName']);
    }
}
