<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Contract;

interface CommandContract
{
    public function customerId(): int;
}
