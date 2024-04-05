<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\ServiceBus\ContextService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ServiceBusCommandWithContext extends ServiceBusCommand
{
    public const CUSTOMER_CODE = 'customer';

    public function __construct(
        ElasticrLogger $logger,
        protected readonly ContextService $contextService
    ) {
        parent::__construct($logger);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->contextService->setContext($this->context());
        return parent::execute($input, $output);
    }
}
