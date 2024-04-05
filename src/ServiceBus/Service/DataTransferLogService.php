<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Service;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\ServiceBus\ContextService;
use Elasticr\ServiceBus\ServiceBus\ValueObject\AuditLogRecordDataTransferPayload;
use Elasticr\ServiceBus\Support\Constant\ElasticrServiceBusLogRecordTypes;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DataTransferLogService
{
    public function __construct(
        private readonly ElasticrLogger $logger,
        private readonly ContextService $contextService,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function log(string $message, string $payloadData): void
    {
        $this->logger->info(
            $this->translator->trans('elasticr.service_bus.' . $message),
            $this->contextService->context(),
            ElasticrServiceBusLogRecordTypes::DATA_TRANSFERS,
            null,
            new AuditLogRecordDataTransferPayload($payloadData)
        );
    }
}
