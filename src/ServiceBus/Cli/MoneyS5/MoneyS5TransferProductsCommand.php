<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\MoneyS5;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\MoneyS5\Facade\MoneyS5ProductsFacade;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(name: 'eesb:moneys5:products:transfer')]
final class MoneyS5TransferProductsCommand extends ServiceBusCommand
{
    public function __construct(
        private readonly MoneyS5ProductsFacade $productsFacade,
        ElasticrLogger $logger
    ) {
        parent::__construct($logger);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command transfers new products from MoneyS5')
            ->addArgument(self::CUSTOMER_CODE, InputArgument::REQUIRED, 'MoneyS5 customer code');
    }

    protected function executeCommand(InputInterface $input): void
    {
        $this->productsFacade->transferProducts($input->getArgument(self::CUSTOMER_CODE));
    }

    protected function failedMessage(InputInterface $input): string
    {
        return "Sync products from MoneyS5 failed for customer {$input->getArgument(self::CUSTOMER_CODE)}";
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
