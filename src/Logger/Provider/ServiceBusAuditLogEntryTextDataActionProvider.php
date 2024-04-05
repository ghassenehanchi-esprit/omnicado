<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Logger\Provider;

use Elasticr\Logger\Contract\AuditLogEntryTextDataActionProviderContract;
use Elasticr\ServiceBus\MoneyS5\Constant\AuditLogEntryDataActions;

final class ServiceBusAuditLogEntryTextDataActionProvider implements AuditLogEntryTextDataActionProviderContract
{
    public function provide(): array
    {
        return [
            AuditLogEntryDataActions::TRANSFER_VALIDATION->value,
        ];
    }
}
