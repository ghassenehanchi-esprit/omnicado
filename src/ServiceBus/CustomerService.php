<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus;

use RuntimeException;

final class CustomerService
{
    private ?int $customerId = null;

    public function customerIdOrNull(): ?int
    {
        return $this->customerId;
    }

    public function customerId(): int
    {
        if ($this->customerId === null) {
            throw new RuntimeException();
        }

        return $this->customerId;
    }

    public function setCustomerId(int $customerId): void
    {
        $this->customerId = $customerId;
    }
}
