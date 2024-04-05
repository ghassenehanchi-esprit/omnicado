<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\Shoptet;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommand;
use Elasticr\ServiceBus\Shoptet\Facade\OrdersFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(name: 'eesb:shoptet:orders:transfer')]
final class TransferOrdersByTransferRulesCommand extends ServiceBusCommand
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
            ->setDescription('This command transfers new orders by transfer rules from shoptet')
            ->addArgument(self::CUSTOMER_CODE, InputArgument::REQUIRED, 'Shoptet customer code');
    }

    protected function executeCommand(InputInterface $input): void
    {
        $this->ordersFacade->transferOrdersByTransferRules($input->getArgument(self::CUSTOMER_CODE));
    }

    protected function failedMessage(InputInterface $input): string
    {
        return "Sync orders from Shoptet failed {$input->getArgument(self::CUSTOMER_CODE)}";
    }

    protected function successfulMessage(): string
    {
        return 'All new orders were inserted';
    }

    protected function context(): string
    {
        return 'elasticr.actions.service_bus.transfer_orders';
    }
}
