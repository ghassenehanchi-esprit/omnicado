<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\Shoptet;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\Logger\ValueObject\AuditLogExceptionErrorData;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommand;
use Elasticr\ServiceBus\Shoptet\Facade\OrdersFacade;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'eesb:shoptet:orders:list-new')]
final class ListNewOrdersCommand extends Command
{
    private OrdersFacade $ordersFacade;

    private ElasticrLogger $logger;

    public function __construct(OrdersFacade $ordersFacade, ElasticrLogger $logger)
    {
        parent::__construct();
        $this->ordersFacade = $ordersFacade;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command lists new orders')
            ->addArgument(ServiceBusCommand::CUSTOMER_CODE, InputArgument::REQUIRED, 'Shoptet customer code');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $orders = $this->ordersFacade->listNewOrders($input->getArgument(ServiceBusCommand::CUSTOMER_CODE));

            $output->writeln(['New orders', '===================']);
            foreach ($orders as $order) {
                $output->writeln(sprintf('ORDER NUMBER: %s, Creation time: %s', $order->number(), $order->creationTime()));
            }
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage(), 'elasticr.service_bus.list.new_orders', '', null, new AuditLogExceptionErrorData($exception));

            $output->writeln(['<error>New orders list failed</error>', sprintf('<error>ERROR: %s</error>', $exception->getMessage())]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
