<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Command;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;
use Elasticr\ServiceBus\ServiceBus\Model\InventoryReportItemDto;

final class GenerateInventoryReportCommand implements CommandContract
{
    /**
     * @var InventoryReportItemDto[]
     */
    private array $data;

    private int $customerId;

    private string $sourceSystem;

    /**
     * @param InventoryReportItemDto[] $data
     */
    public function __construct(array $data, string $sourceSystem, int $customerId)
    {
        $this->customerId = $customerId;
        $this->data = $data;
        $this->sourceSystem = $sourceSystem;
    }

    /**
     * @return InventoryReportItemDto[]
     */
    public function data(): array
    {
        return $this->data;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }

    public function sourceSystem(): string
    {
        return $this->sourceSystem;
    }
}
