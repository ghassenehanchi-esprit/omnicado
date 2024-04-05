<?php

declare(strict_types=1);

namespace Elasticr\ServiceBus\ServiceBus\Cli\Shoptet;

use Elasticr\Logger\ElasticrLogger;
use Elasticr\ServiceBus\ServiceBus\Cli\ServiceBusCommand;
use Elasticr\ServiceBus\ServiceBus\CustomerConfigFinder;
use Elasticr\ServiceBus\ServiceBus\Exception\CustomerNotFoundException;
use Elasticr\ServiceBus\ServiceBus\Repository\CustomerRepository;
use Elasticr\ServiceBus\Shoptet\Api\WebhooksApiService;
use Elasticr\ServiceBus\Shoptet\ValueObject\ShoptetConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(name: 'eesb:shoptet:delete-webhook')]
final class DeleteWebhookCommand extends ServiceBusCommand
{
    /**
     * @var string
     */
    public const WEBHOOOK_ID = 'webhookId';

    private int $webhookId;

    private WebhooksApiService $apiService;

    private CustomerConfigFinder $customerConfigFinder;

    private CustomerRepository $customerRepository;

    public function __construct(WebhooksApiService $apiService, ElasticrLogger $logger, CustomerConfigFinder $customerConfigFinder, CustomerRepository $customerRepository)
    {
        parent::__construct($logger);
        $this->apiService = $apiService;
        $this->customerConfigFinder = $customerConfigFinder;
        $this->customerRepository = $customerRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('This command deletes webhook')
            ->addArgument(self::CUSTOMER_CODE, InputArgument::REQUIRED, 'Shoptet customer code')
            ->addArgument(self::WEBHOOOK_ID, InputArgument::REQUIRED, 'Webhook ID');
    }

    protected function executeCommand(InputInterface $input): void
    {
        $customer = $this->customerRepository->findByCode($input->getArgument(self::CUSTOMER_CODE));

        if ($customer === null) {
            throw new CustomerNotFoundException('Customer with code: ' . $customer . ' does not exist');
        }

        /** @var ShoptetConfig $config */
        $config = $this->customerConfigFinder->find($customer->id(), ShoptetConfig::NAME);

        $this->webhookId = $input->getArgument(self::WEBHOOOK_ID);
        $this->apiService->deleteWebhook($config->eshopId(), $this->webhookId);
    }

    protected function failedMessage(InputInterface $input): string
    {
        return "Deleting webhook failed for customer {$input->getArgument(self::CUSTOMER_CODE)}";
    }

    protected function successfulMessage(): string
    {
        return sprintf('<info>Webhook ID %s successfully deleted</info>', $this->webhookId);
    }

    protected function context(): string
    {
        return 'elasticr.service_bus.delete.webhook';
    }
}
