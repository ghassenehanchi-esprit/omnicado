<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Command;

use Elasticr\ServiceBus\ServiceBus\Contract\CommandContract;
use Elasticr\ServiceBus\ServiceBus\Model\DocumentDto;

final class UpdateDeliveryNoteDocumentCommand implements CommandContract
{
    public function __construct(
        private readonly DocumentDto $document,
        private readonly int $customerId
    ) {
    }

    public function document(): DocumentDto
    {
        return $this->document;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }
}
