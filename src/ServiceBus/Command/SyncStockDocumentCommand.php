<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Command;

use Cake\Chronos\Chronos;
use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;
use Elasticr\ServiceBus\ServiceBus\Model\StockDocumentDto;

final class SyncStockDocumentCommand implements CommandContract
{
    private StockDocumentDto $documentDto;

    private int $customerId;

    public function __construct(StockDocumentDto $documentDto, int $customerId)
    {
        $this->customerId = $customerId;
        $this->documentDto = $documentDto;
    }

    public function documentDto(): StockDocumentDto
    {
        return $this->documentDto;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }

    public function documentLastDtClosed(): Chronos
    {
        if ($this->documentDto->relatedDoc() !== null) {
            if ($this->documentDto->dtClosed()->lessThan($this->documentDto->relatedDoc()->dtClosed())) {
                return $this->documentDto->relatedDoc()->dtClosed();
            }

            return $this->documentDto->dtClosed();
        }

        return $this->documentDto->dtClosed();
    }
}
