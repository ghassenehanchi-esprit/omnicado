<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Facade;

use Elasticr\ServiceBus\ServiceBus\Entity\Customer;
use Elasticr\ServiceBus\Support\Provider\CustomerProvider;

abstract class Facade
{
    public function __construct(
        private readonly CustomerProvider $customerProvider,
    ) {
    }

    protected function customer(string $customerCode): Customer
    {
        return $this->customerProvider->provideByCode($customerCode);
    }
}
