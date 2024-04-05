<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\WooCommerce\Api;

use Elasticr\ServiceBus\WooCommerce\ValueObject\WooCommerceConfig;

trait WooCommerceConfigTrait
{
    private function getSimpleConfig(): WooCommerceConfig
    {
        return new WooCommerceConfig('', '', '', null);
    }
}
