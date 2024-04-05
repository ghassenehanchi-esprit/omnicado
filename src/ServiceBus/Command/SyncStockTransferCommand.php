<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Command;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;
use Elasticr\ServiceBus\ServiceBus\Model\StockTransferDto;

final class SyncStockTransferCommand implements CommandContract
{
    private StockTransferDto $stockTransferDto;

    private int $customerId;

    public function __construct(StockTransferDto $stockTransferDto, int $customerId)
    {
        $this->customerId = $customerId;
        $this->stockTransferDto = $stockTransferDto;
    }

    public function stockTransfer(): StockTransferDto
    {
        return $this->stockTransferDto;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }
}
