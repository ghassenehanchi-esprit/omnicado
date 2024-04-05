<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\Flexibee;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\Flexibee\Facade\ProductsFacade;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(name: 'eesb:flexibee:products:transfer')]
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
            ->setDescription('This command transfers new products from Abra Flexibee')
            ->addArgument(self::CUSTOMER_CODE, InputArgument::REQUIRED, 'Abra Flexibee customer code');
    }

    protected function executeCommand(InputInterface $input): void
    {
        $this->productsFacade->transferProducts($input->getArgument(self::CUSTOMER_CODE));
    }

    protected function failedMessage(InputInterface $input): string
    {
        return "Sync products from Flexibee failed for customer {$input->getArgument(self::CUSTOMER_CODE)}";
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
