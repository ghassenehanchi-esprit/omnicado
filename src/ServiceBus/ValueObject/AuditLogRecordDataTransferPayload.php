<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\ValueObject;

use Elasticr\Logger\Contract\AuditLogRecordDataContract;

final class AuditLogRecordDataTransferPayload implements AuditLogRecordDataContract
{
    public function __construct(
        private readonly string $payload
    ) {
    }

    public function toArray(): array
    {
        return ['payload' => $this->payload];
    }
}
