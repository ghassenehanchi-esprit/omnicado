<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Model;

final class PriceDto
{
    private ValueDto $value;

    private string $currency;

    public function __construct(ValueDto $value, string $currency)
    {
        $this->value = $value;
        $this->currency = $currency;
    }

    public function value(): ValueDto
    {
        return $this->value;
    }

    public function currency(): string
    {
        return $this->currency;
    }
}
