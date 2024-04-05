<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Exception;

use Exception;

abstract class ShoptetApiException extends Exception
{
    public function __construct(string $error, string $description)
    {
        parent::__construct(sprintf('%s - %s', $error, $description));
    }
}
