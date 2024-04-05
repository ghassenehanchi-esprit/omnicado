<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Command;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentDto;

final class SyncReceiptCommand implements CommandContract
{
    private DocumentDto $documentDto;

    private int $customerId;

    public function __construct(DocumentDto $documentDto, int $customerId)
    {
        $this->customerId = $customerId;
        $this->documentDto = $documentDto;
    }

    public function document(): DocumentDto
    {
        return $this->documentDto;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }
}
