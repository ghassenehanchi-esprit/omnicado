<?php

declare(strict_types=1);

namespace Tests\Elasticr\ServiceBus\Unit\Flexibee\Api;

use Elasticr\ServiceBus\Flexibee\Api\Filter\FlexibeeOrdersListFilter;
use Elasticr\ServiceBus\Flexibee\ValueObject\FlexibeeConfig;
use Elasticr\ServiceBus\Flexibee\ValueObject\FlexibeeDocTypeConfig;
use Elasticr\ServiceBus\Flexibee\ValueObject\FlexibeePurchaseOrdersConfig;
use Elasticr\ServiceBus\Flexibee\ValueObject\FlexibeeSellOrdersConfig;

trait FlexibeeConfigTrait
{
    private function getConfig(): FlexibeeConfig
    {
        return new FlexibeeConfig(
            '',
            '',
            [],
            new FlexibeeSellOrdersConfig(new FlexibeeOrdersListFilter([]), '', ''),
            new FlexibeePurchaseOrdersConfig(new FlexibeeOrdersListFilter([]), '', ''),
            [],
            [
                'VZSPF05PDX' => new FlexibeeDocTypeConfig('prijem', 74, 'SKLAD-HANDLING', 'prijemPoObch', ''),
                'VZSPF98ZA' => new FlexibeeDocTypeConfig('prijem', 66, 'SKLAD-SK', 'prijemHoly', '49'),
                'VZSVF97ZA' => new FlexibeeDocTypeConfig('vydej', 66, 'SKLAD-CZ', 'vydejHoly', '49'),
                'VZSVF05PV97' => new FlexibeeDocTypeConfig('vydej', 2, 'SKLAD-CZ', 'vydejPrevod', ''),
            ],
            []
        );
    }
}
