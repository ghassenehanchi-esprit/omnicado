<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\Eso9;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\Eso9\Service\InventoryReportService;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(name: 'eesb:eso9:inventory:report')]
final class InventoryReportCommand extends ServiceBusCommand
{
    public function __construct(
        private readonly InventoryReportService $inventoryReportService,
        ElasticrLogger $logger
    ) {
        parent::__construct($logger);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command creates Eso inventory report')
            ->addArgument(self::CUSTOMER_CODE, InputArgument::REQUIRED, 'Customer code');
    }

    protected function executeCommand(InputInterface $input): void
    {
        $this->inventoryReportService->generateReport($input->getArgument(self::CUSTOMER_CODE));
    }

    protected function failedMessage(InputInterface $input): string
    {
        return "Report generation failed for customer {$input->getArgument(self::CUSTOMER_CODE)}";
    }

    protected function successfulMessage(): string
    {
        return 'Report was generated';
    }

    protected function context(): string
    {
        return 'elasticr.service_bus.generate.report';
    }
}
