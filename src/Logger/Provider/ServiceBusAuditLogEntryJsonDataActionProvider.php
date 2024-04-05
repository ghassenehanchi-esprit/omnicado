<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\Logger\Provider;

use Elasticr\Logger\Contract\AuditLogEntryJsonDataActionProviderContract;

final class ServiceBusAuditLogEntryJsonDataActionProvider implements AuditLogEntryJsonDataActionProviderContract
{
    public function provide(): array
    {
        return [
            'elasticr.service_bus.transfer.data',
            'elasticr.actions.service_bus.sync_products',
            'elasticr.actions.service_bus.transfer_orders',
            'elasticr.actions.service_bus.transfer_returns',
            'elasticr.actions.service_bus.transfer_stock_documents',
            'elasticr.actions.service_bus.transfer_samples',
            'elasticr.actions.service_bus.transfer_issued_invoices',
        ];
    }
}
