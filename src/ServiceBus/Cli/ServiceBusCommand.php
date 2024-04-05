<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\Logger\ValueObject\AuditLogExceptionErrorData;
use Elasticr\ServiceBus\Support\Constant\ElasticrServiceBusLogRecordTypes;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ServiceBusCommand extends Command
{
    public const CUSTOMER_CODE = 'customer';

    public function __construct(
        protected readonly ElasticrLogger $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->executeCommand($input);
            $output->writeln($this->successfulMessage());
        } catch (Exception $exception) {
            $this->logger->critical(
                $this->failedMessage($input),
                $this->context(),
                ElasticrServiceBusLogRecordTypes::DATA_TRANSFERS,
                null,
                new AuditLogExceptionErrorData($exception)
            );

            $output->writeln('<error>Sync failed</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function getCustomerCode(InputInterface $input): string
    {
        return $input->getArgument(self::CUSTOMER_CODE);
    }

    abstract protected function executeCommand(InputInterface $input): void;

    abstract protected function failedMessage(InputInterface $input): string;

    abstract protected function successfulMessage(): string;

    abstract protected function context(): string;
}
