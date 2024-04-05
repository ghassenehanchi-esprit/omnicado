<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\MoneyS5;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\MoneyS5\Facade\MoneyS5IssuedInvoicesFacade;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommandWithContext;
use Elasticr\ServiceBus\ServiceBus\ContextService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(name: 'eesb:moneys5:issued-invoice:transfer')]
final class MoneyS5TransferIssuedInvoicesCommand extends ServiceBusCommandWithContext
{
    public function __construct(
        private readonly MoneyS5IssuedInvoicesFacade $issuedInvoicesFacade,
        ElasticrLogger $logger,
        ContextService $contextService
    ) {
        parent::__construct($logger, $contextService);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command transfers new issued invoices from MoneyS5')
            ->addArgument(self::CUSTOMER_CODE, InputArgument::REQUIRED, 'MoneyS5 customer code');
    }

    protected function executeCommand(InputInterface $input): void
    {
        $this->issuedInvoicesFacade->transferIssuedInvoices($input->getArgument(self::CUSTOMER_CODE));
    }

    protected function failedMessage(InputInterface $input): string
    {
        return "Sync issued invoices from MoneyS5 failed for customer {$input->getArgument(self::CUSTOMER_CODE)}";
    }

    protected function successfulMessage(): string
    {
        return 'All issued invoices were transferred';
    }

    protected function context(): string
    {
        return 'elasticr.actions.service_bus.transfer_issued_invoices';
    }
}
