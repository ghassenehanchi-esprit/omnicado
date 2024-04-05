<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\WooCommerce;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommand;
use Elasticr\ServiceBus\WooCommerce\Facade\OrdersFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(name: 'eesb:woocommerce:orders:transfer-new')]
final class WooCommerceTransferNewOrdersCommand extends ServiceBusCommand
{
    public function __construct(
        private readonly OrdersFacade $ordersFacade,
        ElasticrLogger $logger
    ) {
        parent::__construct($logger);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command transfers new orders from WooCommerce')
            ->addArgument(self::CUSTOMER_CODE, InputArgument::REQUIRED, 'WooCommerce customer code');
    }

    protected function executeCommand(InputInterface $input): void
    {
        $this->ordersFacade->transferNewOrders($input->getArgument(self::CUSTOMER_CODE));
    }

    protected function failedMessage(InputInterface $input): string
    {
        return "Sync orders from WooCommerce failed for customer {$input->getArgument(self::CUSTOMER_CODE)}";
    }

    protected function successfulMessage(): string
    {
        return 'All new orders were transferred';
    }

    protected function context(): string
    {
        return 'elasticr.actions.service_bus.transfer_orders';
    }
}
