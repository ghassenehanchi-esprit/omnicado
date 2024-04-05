<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Contract;

interface ConfigContract
{
    public function name(): string;

    public function title(): string;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
