<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\Eso9;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\Eso9\Service\SyncStockDocumentsService;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(name: 'eesb:eso9:sync:stock:stock-documents')]
final class SyncStockDocumentsCommand extends ServiceBusCommand
{
    public function __construct(
        private readonly SyncStockDocumentsService $service,
        ElasticrLogger $logger
    ) {
        parent::__construct($logger);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command sync stock documents from eso9')
            ->addArgument(self::CUSTOMER_CODE, InputArgument::REQUIRED, 'Eso9 customer code');
    }

    protected function executeCommand(InputInterface $input): void
    {
        $this->service->execute($input->getArgument(self::CUSTOMER_CODE));
    }

    protected function failedMessage(InputInterface $input): string
    {
        return "Sync stock documents from Eso9 failed for customer {$input->getArgument(self::CUSTOMER_CODE)}";
    }

    protected function successfulMessage(): string
    {
        return 'All documents were updated';
    }

    protected function context(): string
    {
        return 'elasticr.service_bus.sync.stock_documents';
    }
}
