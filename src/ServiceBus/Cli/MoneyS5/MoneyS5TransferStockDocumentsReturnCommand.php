<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\MoneyS5;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\MoneyS5\Facade\MoneyS5StockDocumentsReturnFacade;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommandWithContext;
use Elasticr\ServiceBus\ServiceBus\ContextService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(name: 'eesb:moneys5:stock-document-return:transfer')]
final class MoneyS5TransferStockDocumentsReturnCommand extends ServiceBusCommandWithContext
{
    public function __construct(
        private readonly MoneyS5StockDocumentsReturnFacade $stockDocumentsReturnFacade,
        ElasticrLogger $logger,
        ContextService $contextService
    ) {
        parent::__construct($logger, $contextService);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command transfers new stock documents as return from MoneyS5')
            ->addArgument(self::CUSTOMER_CODE, InputArgument::REQUIRED, 'MoneyS5 customer code');
    }

    protected function executeCommand(InputInterface $input): void
    {
        $this->stockDocumentsReturnFacade->transferStockDocumentsReturn($input->getArgument(self::CUSTOMER_CODE));
    }

    protected function failedMessage(InputInterface $input): string
    {
        return "Sync stock documents as return from MoneyS5 failed for customer {$input->getArgument(self::CUSTOMER_CODE)}";
    }

    protected function successfulMessage(): string
    {
        return 'All stock documents as return were transferred';
    }

    protected function context(): string
    {
        return 'elasticr.actions.service_bus.transfer_returns';
    }
}
