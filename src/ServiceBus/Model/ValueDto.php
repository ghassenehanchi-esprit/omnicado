<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class ValueDto
{
    public function __construct(
        private readonly float $withoutVat,
        private readonly float $withVat
    ) {
    }

    public function withVat(): float
    {
        return $this->withVat;
    }

    public function withoutVat(): float
    {
        return $this->withoutVat;
    }
}
