<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\Eso9;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\Eso9\Facade\Eso9OrdersFacade;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(name: 'eesb:eso9:orders:transfer-new')]
final class Eso9TransferNewOrdersCommand extends ServiceBusCommand
{
    public function __construct(
        private readonly Eso9OrdersFacade $ordersFacade,
        ElasticrLogger $logger
    ) {
        parent::__construct($logger);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command transfers new orders from Eso9')
            ->addArgument(self::CUSTOMER_CODE, InputArgument::REQUIRED, 'Eso9 customer code');
    }

    protected function executeCommand(InputInterface $input): void
    {
        $this->ordersFacade->transferNewOrders($input->getArgument(self::CUSTOMER_CODE));
    }

    protected function failedMessage(InputInterface $input): string
    {
        return "Transferring orders from Eso9 failed for customer {$input->getArgument(self::CUSTOMER_CODE)}";
    }

    protected function successfulMessage(): string
    {
        return 'All new orders were transferred';
    }

    protected function context(): string
    {
        return 'elasticr.service_bus.transfer.orders';
    }
}
