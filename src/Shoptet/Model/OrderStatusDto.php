<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Shoptet\Model;

final class OrderStatusDto
{
    private int $id;

    private int $eshopId;

    private string $name;

    private ?bool $paid;

    public function __construct(int $id, int $eshopId, string $name, ?bool $paid)
    {
        $this->id = $id;
        $this->name = $name;
        $this->paid = $paid;
        $this->eshopId = $eshopId;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function eshopId(): int
    {
        return $this->eshopId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function paid(): ?bool
    {
        return $this->paid;
    }
}
