<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\Flexibee;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\Flexibee\Facade\SellOrdersFacade;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(name: 'eesb:flexibee:sell-orders:transfer')]
final class TransferSellOrdersCommand extends ServiceBusCommand
{
    public function __construct(
        private readonly SellOrdersFacade $facade,
        ElasticrLogger $logger
    ) {
        parent::__construct($logger);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command transfers sell orders from Abra Flexibee')
            ->addArgument(self::CUSTOMER_CODE, InputArgument::REQUIRED, 'Abra Flexibee customer code');
    }

    protected function executeCommand(InputInterface $input): void
    {
        $this->facade->transfer($input->getArgument(self::CUSTOMER_CODE));
    }

    protected function failedMessage(InputInterface $input): string
    {
        return "Sync sell orders from Flexibee failed for customer {$input->getArgument(self::CUSTOMER_CODE)}";
    }

    protected function successfulMessage(): string
    {
        return 'All sell orders were transfered';
    }

    protected function context(): string
    {
        return 'elasticr.service_bus.sync.sell_orders';
    }
}
