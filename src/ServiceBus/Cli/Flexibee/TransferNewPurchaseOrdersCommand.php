<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\Flexibee;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\Flexibee\Facade\PurchaseOrdersFacade;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(name: 'eesb:flexibee:orders:transfer-new')]
final class TransferNewPurchaseOrdersCommand extends ServiceBusCommand
{
    public function __construct(
        private readonly PurchaseOrdersFacade $ordersFacade,
        ElasticrLogger $logger
    ) {
        parent::__construct($logger);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command transfers new orders from Flexibee')
            ->addArgument(self::CUSTOMER_CODE, InputArgument::REQUIRED, 'Flexibee customer code');
    }

    protected function executeCommand(InputInterface $input): void
    {
        $this->ordersFacade->transferNewOrders($input->getArgument(self::CUSTOMER_CODE));
    }

    protected function failedMessage(InputInterface $input): string
    {
        return "Sync orders from Flexibee failed for customer {$input->getArgument(self::CUSTOMER_CODE)}";
    }

    protected function successfulMessage(): string
    {
        return 'All new orders were transfered';
    }

    protected function context(): string
    {
        return 'elasticr.actions.service_bus.transfer_orders';
    }
}
