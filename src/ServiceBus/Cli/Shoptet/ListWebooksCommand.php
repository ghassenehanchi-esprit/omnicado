<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\Shoptet;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\Logger\ValueObject\AuditLogExceptionErrorData;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Exception\CustomerNotFoundException;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\Shoptet\Api\WebhooksApiService;
use Elasticr\ServiceBus\Shoptet\ValueObject\ShoptetConfig;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'eesb:shoptet:list-webhooks')]
final class ListWebooksCommand extends Command
{
    private WebhooksApiService $apiService;

    private ElasticrLogger $logger;

    private CustomerConfigFinder $customerConfigFinder;

    private CustomerRepository $customerRepository;

    public function __construct(WebhooksApiService $apiService, ElasticrLogger $logger, CustomerConfigFinder $customerConfigFinder, CustomerRepository $customerRepository)
    {
        parent::__construct();
        $this->apiService = $apiService;
        $this->logger = $logger;
        $this->customerConfigFinder = $customerConfigFinder;
        $this->customerRepository = $customerRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command lists registered webhooks')
            ->addArgument(ServiceBusCommand::CUSTOMER_CODE, InputArgument::REQUIRED, 'Shoptet customer code');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $customer = $this->customerRepository->findByCode($input->getArgument(ServiceBusCommand::CUSTOMER_CODE));

            if ($customer === null) {
                throw new CustomerNotFoundException('Customer with code: ' . $customer . ' does not exist');
            }

            /** @var ShoptetConfig $config */
            $config = $this->customerConfigFinder->find($customer->id(), ShoptetConfig::NAME);

            $webhooks = $this->apiService->listWebhooks($config->eshopId());

            $output->writeln(['Webhooks', '===================']);
            foreach ($webhooks as $webhook) {
                $output->writeln(sprintf(
                    'ID: %s, EVENT: %s, URL: %s, CREATED: %s',
                    $webhook->id(),
                    $webhook->event(),
                    $webhook->url(),
                    $webhook->created()->format('d.m.Y H:i:s')
                ));
            }
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage(), 'elasticr.service_bus.list.webhooks', '', null, new AuditLogExceptionErrorData($exception));

            $output->writeln(['<error>Webhooks list failed</error>', sprintf('<error>ERROR: %s</error>', $exception->getMessage())]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
