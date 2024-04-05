<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Provider\Contract;

interface AuthTokenProviderContract
{
    public function getToken(int $eshopId): string;
}
