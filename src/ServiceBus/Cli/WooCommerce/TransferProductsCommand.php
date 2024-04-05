<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\WooCommerce;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommand;
use Elasticr\ServiceBus\WooCommerce\Facade\ProductsFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(name: 'eesb:woocommerce:products:transfer')]
final class TransferProductsCommand extends ServiceBusCommand
{
    public function __construct(
        private readonly ProductsFacade $productsFacade,
        ElasticrLogger $logger
    ) {
        parent::__construct($logger);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command transfers new products from WooCommerce')
            ->addArgument(self::CUSTOMER_CODE, InputArgument::REQUIRED, 'WooCommerce customer code');
    }

    protected function executeCommand(InputInterface $input): void
    {
        $this->productsFacade->transferProducts($input->getArgument(self::CUSTOMER_CODE));
    }

    protected function failedMessage(InputInterface $input): string
    {
        return "Sync products from WooCommerce failed for customer {$input->getArgument(self::CUSTOMER_CODE)}";
    }

    protected function successfulMessage(): string
    {
        return 'All products were transferred';
    }

    protected function context(): string
    {
        return 'elasticr.actions.service_bus.sync_products';
    }
}
