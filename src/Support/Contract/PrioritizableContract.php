<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Support\Contract;

interface PrioritizableContract
{
    public function priority(): int;
}
